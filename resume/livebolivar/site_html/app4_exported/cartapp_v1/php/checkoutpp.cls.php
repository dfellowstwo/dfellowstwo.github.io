<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Extension of Page for checking out with PayPal.
*
* @version $Revision: 2813 $
* @author Cees de Gruijter
* @category SCC
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/


define( 'PP_API_VERSION', '64.0');

class Checkout {

	var $resArray;						// responses from PayPall
	var $page;
	var $cart;

	function Checkout ( &$page ) {

		$this->page =& $page;
		$this->cart =& $page->cart;

		if( isset( $_SESSION[PAYMENT]) )
			$this->resArray = unserialize( $_SESSION[PAYMENT] );
		else
			$this->resArray = array();
	}


	function authorizePayment ( ) {

		// some data validation
		if( $this->cart->getGrandTotalCart() == 0 ||
			$this->page->getConfigS('shopcurrency') == "" )
			return "FAILURE";

		// csrt lines may not have 0 quantities
		foreach( $this->cart->getPairProductIdGroupId() as $article ) {

			if( (int) $this->cart->getSubtotalPriceProduct( $article['cartid'] ) == 0 ) {

				$this->page->setCartMessage( _T("You can't checkout with items that have a price of '0.00'. Please delete them from the cart.") );
				return "WARNING";

			}
		}

		// new transaction start - clear any old data
		$this->clearSessionData();

		// from this point onwards, the cart can NOT be changed anymore
		$this->page->lockCart( true );

		// my url, must be without any query part
		$url = $this->page->getFullUrl( false, false );

		// save the amount we send out, only used for display purposes
		$this->resArray['PAYMENTREQUEST_0_AMT'] = formatMoney( $this->cart->getGrandTotalCart(), 100, 'en' );

		$returnURL = urlencode($url);
		$cancelURL = urlencode($url . '?cancel=1');

		$nvpstr = '&PAYMENTREQUEST_0_PAYMENTACTION=Sale'
		 		. '&RETURNURL=' . $returnURL
				. '&CANCELURL=' . $cancelURL
				. '&PAYMENTREQUEST_0_CURRENCYCODE=' . $this->page->getConfigS('shopcurrency')
				. '&ALLOWNOTE=1'					// allow buyer to add a note
				. '&LANDINGPAGE=Billing';			// can be Billing or Login

		if( $this->page->getConfigS( 'transaction_log' ) ) {
			$nvpstr .= '&PAYMENTREQUEST_0_NOTIFYURL=' . urlencode( $this->page->getFullUrl( 'servicepp.php', false, true ) );
		}

		// add line item info with the SetExpressCheckout call because of a possible
		// redirect to GiroPay before the DoExpressCheckoutPayment call
		$this->addLineItems( $nvpstr );
		$this->addTotals ( $nvpstr ) ;

		if( $this->page->getConfigS( 'PayPal', 'USE_GIROPAY') ) {
			$nvpstr .= '&GIROPAYSUCCESSURL=' . urlencode( $url . '?gpok=1' )		// redirect URL after a successful giropay payment.
					 . '&GIROPAYCANCELURL=' . urlencode( $url . '?gpok=0' )			// redirect URL after a payment is cancelled or fails.
					 . '&BANKTXNPENDINGURL=' . urlencode( $url . '?gpok=2' );		// redirect URL after a bank transfer payment.
		}
		//writeErrorLog( $nvpstr );

		/* Make the call to PayPal to set the Express Checkout token
		 * If the API call succeded, then redirect the buyer to PayPal
		 * to begin to authorize payment.  If an error occured, show the resulting errors
		 */
		$this->hash_call( "SetExpressCheckout", $nvpstr );
		#print_r($this->resArray);
		$ack = strtoupper( $this->resArray["ACK"] );

		return $ack;
	}


	function redirectPayPal ( $withAjax = false ) {

		if( !isset($this->resArray["TOKEN"]) ) return;

		// make sure the cart stays locked
		$this->page->lockCart(true);

		// Redirect to paypal.com
		$payPalURL = $this->page->getConfigS('PayPal', 'PAYPAL_URL') . urldecode($this->resArray["TOKEN"]);

		header("Location: " . $payPalURL);
	}


	function getPaymentDetails ( ) {

		// make sure the cart stays locked
		$this->page->lockCart(true);

		$nvpstr = '&TOKEN=' . urlencode( $this->resArray['TOKEN'] );
		$this->hash_call( "GetExpressCheckoutDetails", $nvpstr );

		// check for the 'REDIRECTREQUIRED' field to learn if GIROPAY was selected by the buyer

		return strtoupper( $this->resArray["ACK"] );
	}


	function finalizePayment ( ) {

		// make sure the cart stays locked
		$this->page->lockCart(true);

		// start building the name-value pair string
		$nvpstr = '&TOKEN=' . urlencode( $this->resArray['TOKEN'] )
				. '&PAYERID=' . urlencode( $this->resArray['PAYERID'] )
				. '&PAYMENTREQUEST_0_PAYMENTACTION=Sale'
				. '&PAYMENTREQUEST_0_CURRENCYCODE=' . urlencode( $this->page->getConfigS('shopcurrency') )
				. '&IPADDRESS=' . urlencode( $_SERVER['SERVER_NAME'] );

		$this->addLineItems( $nvpstr );
		$this->addTotals( $nvpstr ) ;

		// Call PayPal
		$this->hash_call( "DoExpressCheckoutPayment", $nvpstr );
		#print_r($this->resArray);

		$ack = strtoupper( $this->resArray["ACK"] );

		if( $ack == 'SUCCESS' ) {
			// don't empty the cart now, we need it for notifying shop owner
			unset( $this->resArray['TOKEN'] );					// or else PP may complain about duplicate transactions
			$_SESSION[PAYMENT] = serialize( $this->resArray );	// and make sure the session is also updated
		}
		return $ack;
	}


	function verifyIPN ( ) {

		// echo the query string back and expect VERIFIED if OK
		foreach ( $_POST as $key => $value ) {
		  $req .= '&' . $key . '=' . urlencode( $value );
		}

		$url = $this->page->getConfigS('PayPal', 'PAYPAL_URL');
		$url = substr( $url, 0, strpos( $url, '?cmd=' ) ) . '?cmd=_notify-validate';

		return strcmp( $this->do_call( $req, $url ) , 'VERIFIED' ) == 0;
	}


/*********************** PRIVATE METHODS *********************************/

	function addTotals ( &$nvp ) {

		$itemTax = '&PAYMENTREQUEST_0_TAXAMT=' . urlencode( number_format( $this->cart->getTotalTax() / 100, 2, '.', '') );
		$itemAmt = '&PAYMENTREQUEST_0_ITEMAMT=' . urlencode( number_format( ( $this->cart->getSubtotalPriceCart() + $this->cart->getShippingHandlingTotal() ) / 100, 2, '.', '' ) );

		// note: shipping is now a seperate line-item and included in the grand-total
		//$shipping = '&SHIPPINGAMT=' . urlencode(formatMoney($this->cart->getShippingProducts(), 100, 'en'));
		//$handling = '&HANDLINGAMT=' . urlencode(formatMoney($this->cart->getTotalHandling(), 100, 'en'));
		$grandtotal = '&PAYMENTREQUEST_0_AMT=' . urlencode( number_format( $this->cart->getGrandTotalCart() / 100, 2, '.', '') );
		$nvp .= $grandtotal . $itemAmt . $itemTax /*. $shipping . $handling */;

		// add reference to our transaction log
		$ref = '';
		if( method_exists( $this->page, 'GetTransCode' ) && $this->page->GetTransCode( $ref )  ) {
			$nvp .= '&PAYMENTREQUEST_0_CUSTOM=' . $ref;
		}
	}

	 /*	NOTE: If the line item details do not add up to ITEMAMT or TAXAMT, the line item details are
		discarded, and the transaction is processed using the values of ITEMAMT or TAXAMT.
		The ACK value in the response is set to SuccessWithWarning.
	 */
	function addLineItems ( &$nvp ) {

		/* Line items in cart */
		$nams = ''; 	// name
		$nums = '';		// sequence number
		$qtys = '';		// quantity
		$taxs = '';		// tax
		$amts = '';		// price

		$i = 0;
		foreach( $this->cart->getPairProductIdGroupId() as $article ) {

			$cid =& $article['cartid'];

			$nams .= '&L_PAYMENTREQUEST_0_NAME'   . $i . '=' . urlencode( substr($this->cart->getName($cid, true)
				   . $this->cart->getOptionsAsText($cid, ' / '), 0, 127) );	// PayPal accepts 127 chars max
			$nums .= '&L_PAYMENTREQUEST_0_NUMBER' . $i . '=' . ($i + 1);
			$qtys .= '&L_PAYMENTREQUEST_0_QTY'    . $i . '=' . urlencode( $this->cart->getUnitsOfProduct($cid) );
			$taxs .= '&L_PAYMENTREQUEST_0_TAXAMT' . $i . '=' . urlencode( number_format( $this->cart->getTotalTaxAmountProduct($cid) / 100, 2, '.', '') /
														$this->cart->getUnitsOfProduct($cid) );
			$amts .= '&L_PAYMENTREQUEST_0_AMT'    . $i . '=' . urlencode( number_format( $this->cart->getPrice($cid) / 100, 2, '.', '') );
			$i++;
		}

		// add extra shipping as a line item
		$es_amount = $this->cart->getShippingHandlingTotal();

		if( $es_amount != 0 ) {

			$descr = $this->page->getExtraShipping( $this->page->getExtraShippingIndex(), 127 );
			if( empty( $descr ) ) $descr = _T("Shipping and Handling");

			$nams .= '&L_PAYMENTREQUEST_0_NAME'   . $i . '=' . maxLenEncode( _T("Shipment method: ") . $descr, 127 );
			$nums .= '&L_PAYMENTREQUEST_0_NUMBER' . $i . '=' . ($i + 1);
			$qtys .= '&L_PAYMENTREQUEST_0_QTY'    . $i . '=1';
			$taxs .= '&L_PAYMENTREQUEST_0_TAXAMT' . $i . '=' . urlencode( number_format(  $this->cart->getTaxAmountExtraShipping()  / 100, 2, '.', '' ) );
			$amts .= '&L_PAYMENTREQUEST_0_AMT'    . $i . '=' . urlencode( number_format( $es_amount / 100, 2, '.', '' ) );
			$i++;
		}

		$nvp .= $nams . $nums . $qtys . $taxs . $amts;							// add list items

	}


	/* hash_call: Function to perform the API call to PayPal using API signature
	 * @methodName is name of API  method.
	 * @nvpStr is nvp string.
	 * returns an associative array containing the response from the server.
	 */
	function hash_call ( $methodName, $nvpStr )
	{
		$nvpreq = 'METHOD='	. urlencode( $methodName )
				. '&VERSION=' . urlencode( PP_API_VERSION )
				. '&PWD=' . urlencode( obf( $this->page->getConfigS('PayPal', 'API_PASSWORD' ) ) )
				. '&USER=' . urlencode( $this->page->getConfigS('PayPal', 'API_USERNAME' ) )
				. '&SIGNATURE=' . urlencode( obf($this->page->getConfigS('PayPal', 'API_SIGNATURE' ) ) )
				. $nvpStr;

		#echo $nvpreq;
		$result = $this->do_call( $nvpreq );

		if( $result ) {
			$this->updateResultArray( $result );
		}
	}


	function do_call ( $nvpStr, $url = false )
	{
		if( ! $url ) $url = $this->page->getConfigS('PayPal', 'API_ENDPOINT');

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 120 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );

		// Turning off the server and peer verification(TrustManager Concept).
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );

	    // Proxy will only be enabled if USE_PROXY is set to TRUE
		if( $this->page->getConfigS( 'PayPal', 'USE_PROXY' ) ) {
			curl_setopt ( $ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP );
			curl_setopt ( $ch, CURLOPT_PROXY,
							  $this->page->getConfigS( 'PayPal', 'PROXY_HOST' )
			 				. ":" . $this->page->getConfigS( 'PayPal', 'PROXY_PORT' ) );
		}

		#echo $nvpStr;
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $nvpStr );
		$response = curl_exec( $ch );

		if( curl_errno($ch) ) {
			writeErrorLog( 'PayPal call error - ' . curl_errno( $ch )  . ': ' . curl_error( $ch ) );
			return false;
		} else {
			curl_close($ch);
		}

		return $response;
	}


	function updateResultArray ( $nvpstr )
	{
		#echo "Name-Value pair string: " . $nvpstr;
		while( strlen($nvpstr) ) {
			$keypos = strpos($nvpstr, '=');
			$valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&') : strlen($nvpstr);

			$keyval = substr($nvpstr, 0, $keypos);
			$valval = substr($nvpstr, $keypos + 1, $valuepos - $keypos - 1);

			$this->resArray[urldecode($keyval)] = urldecode($valval);
			$nvpstr = substr($nvpstr, $valuepos + 1);
	     }

		$_SESSION[PAYMENT] = serialize($this->resArray);
	}


	function clearSessionData ( ) {

		if( isset($_SESSION[PAYMENT]) )
			unset($_SESSION[PAYMENT]);
		$this->resArray = array();
	}

}

?>
