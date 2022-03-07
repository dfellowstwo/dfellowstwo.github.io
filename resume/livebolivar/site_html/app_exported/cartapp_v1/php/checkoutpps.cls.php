<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Checking out with PayPal - WPS.
*
*
* @version $Revision: 1866 $
* @author Cees de Gruijter
* @category SCC PRO HOSTED
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

require_once CARTREVISION . '/api/checkoutclass.interface.php';

define( 'PP_API_VERSION', '64.0');

class CheckoutPPS extends CheckoutClassInterface {

	var $lineNum = 1;			// must start with 1
	var $resArray = array();	// PDT response from PayPAll
	var $cart;

	function __construct ( $page ) {

		parent::__construct( $page );
	}


	function getCheckoutFields ( ) {

		if( ! $this->returnUrl || ! $this->cancelUrl ) {
			writeErrorLog( 'ReturnURL and CancelURL must be set before calling Checkout::getCheckoutFields.' );
			return false;
		}

		// make sure the cart stays locked
		$this->page->lockCart( true );

		$fields = '';
		$this->addHeaderToMessage( $fields );
		$this->addProductsToMessage( $fields );
		$this->addShippingHandlingToMessage( $fields );
		$this->addTaxToMessage( $fields );

		#print_r($fields);
		return $fields;

	}

	function getPaymentDataTransfer ( $transactionID ) {

		/* After you get the transaction ID, you post a FORM to PayPal that includes the transaction ID
		 * and your identity token with the following content:
		 * 		<form method=post action="https://www.paypal.com/cgi-bin/webscr">
		 * 		<input type="hidden" name="cmd" value="_notify-synch">
		 * 		<input type="hidden" name="tx" value="TransactionID">
		 * 		<input type="hidden" name="at" value="YourIdentityToken">
		 * 		<input type="submit" value="PDT">
		 * 		</form>
		 */

		// define the POST fields as an url-encoded string
		$myfields = 'cmd=_notify-synch'
				  . '&tx=' . urlencode( $transactionID )
				  . '&at=' . urlencode( obf( $this->page->getConfigS( 'PayPalWPS', 'PDT_TOKEN' ) ) );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->page->getConfigS( 'PayPalWPS', 'URL' ) );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 120 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_TRANSFERTEXT, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );

		// Turn off server and peer verification
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );

	    // Proxy will only be enabled if USE_PROXY is set to TRUE
		if( $this->page->getConfigS( 'PayPalWPS', 'USE_PROXY' ) ) {
			curl_setopt ( $ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP );
			curl_setopt ( $ch, CURLOPT_PROXY,
							  $this->page->getConfigS( 'PayPalWPS', 'PROXY_HOST' )
			 				. ":" . $this->page->getConfigS( 'PayPalWPS', 'PROXY_PORT' ) );
		}

		curl_setopt( $ch, CURLOPT_POSTFIELDS, $myfields );

		$response = curl_exec( $ch );

		if( curl_errno( $ch ) ) {
			die( curl_errno( $ch )  . ': ' . curl_error( $ch ) );
		} else {
			curl_close( $ch );
		}

		/*
		 * The first line in PayPall's reply will be SUCCESS or FAIL. An example
		 * successful response looks like this (HTTP Header has been omitted):
		 * 		SUCCESS
		 * 		first_name=Jane+Doe
		 * 		last_name=Smith
		 * 		payment_status=Completed
		 * 		payer_email=janedoesmith%40hotmail.com
		 * 		payment_gross=3.99
		 * 		mc_currency=USD
		 * 		custom=For+the+purchase+of+the+rare+book+Green+Eggs+%26+Ham
		 */

		$r = explode( "\n", $response );
		if( is_array( $r ) && ( $linecount = count( $r ) ) > 1) {

			// line 0 is SUCCESS or FAIL
			$this->resArray['ACK'] = $r[0];

			for( $i = 1 ; $i < $linecount; $i++ ) {

				if( ( $pos = strpos( $r[ $i ], '=' ) ) !== false ) {
					$this->resArray[ substr( $r[ $i ], 0 , strpos( $r[ $i ], '=' ) ) ] =
							urldecode( substr( $r[ $i ], strpos( $r[ $i ], '=' ) + 1 ) );
				} else if( strpos( $r[ $i ], 'Error:' ) !== false ) {
					 $this->resArray[ 'error' ] = substr( $r[ $i ], strrpos( $r[ $i ], ' ' ) + 1 );
				}
			}

		} else {

			$this->resArray = array();
			$this->page->setCartMessage( _T("The response received from PayPal has a different format than expected: ") . $response );

		}

		if( isset( $this->resArray['error'] ) ) {

			$this->page->setCartMessage = _T("PayPal returned error code: ") . $this->resArray['error'] . '. ';

		}

	}

	/*********************** PRIVATE METHODS *********************************/

	function addHeaderToMessage ( &$fields ) {

		$fields .= '<input type="hidden" name="business"   value="'  . $this->page->getConfigS('PayPalWPS', 'BUSINESS') . '" />'
				 . '<input type="hidden" name="cmd" value="_cart" />'
				 . '<input type="hidden" name="upload" value="1" />'
				 . '<input type="hidden" name="charset" value="utf-8">'
				 . '<input type="hidden" name="return" value="' . $this->returnUrl . '" />'
				 . '<input type="hidden" name="cancel_return" value="' . $this->cancelUrl . '" />'
				 . '<input type="hidden" name="amount" value="' . number_format( $this->cart->getGrandTotalCart(true) / 100, 2, '.', '') . '" />'
				 . '<input type="hidden" name="shipping" value="0.00" />'
				 . '<input type="hidden" name="handling_cart" value="0.00" />'
				 //. '<input type="hidden" name="handling_cart" value="' . number_format( $this->cart->getShippingHandlingTotal() / 100, 2, '.', '') . '" />'
				 //. '<input type="hidden" name=" " value="' . . '" />'
				 //. '<input type="hidden" name=" " value="' . . '" />'
				 //. '<input type="hidden" name=" " value="' . . '" />'
				 //. '<input type="hidden" name=" " value="' . . '" />'
				 . '<input type="hidden" name="currency_code" value="' . $this->page->getConfigS('shopcurrency') . '" />';

		if( ($url = $this->page->getTransactionLogUrl()) != '' ) {
			$fields .= '<input type="hidden" name="notify_url" value="' . $url . '" />';
		}

		if( ($url = $this->page->getShopLogoUrl()) != '' ) {
			$fields .= '<input type="hidden" name="cpp_header image" value="' . $url . '" />';
	    }

		if( isset( $_SESSION[ORDERREFKEY] ) ) {
			$fields .= '<input type="hidden" name="custom" value="' . urlencode( strtoupper( $_SESSION[ORDERREFKEY] ) ) . '" />';
		}
    }


	function addProductsToMessage ( &$nvp ) {

		foreach( $this->cart->getPairProductIdGroupId() as $article ) {

			$cid =& $article['cartid'];

			// PP doesn't allow discount line items, but only discounts associated with another line item
			// that has more value than the discount. However the form doesn't enforce any order between the
			// fields, thus use the cart discount for the sum of all discount items.
			if( $this->cart->getPrice( $cid ) < 0 )			continue;

			// ensure the description + option text is not longer than 127
			$optionstxt = $this->cart->getOptionsAsText( $cid, ' / ' );
			
			$prod_name = $this->cart->getName( $cid);
			if( $this->cart->getDescr( $cid) != '') {
				$prod_name .= ':' . $this->cart->getDescr( $cid);
			}
			
			
			$descr = substr( $prod_name, 0, 127 - strlen( $optionstxt ) ) . $optionstxt;

			// create some sort of product id
			$id = $this->cart->getProductProperty( $cid, 'refcode' );
			if( empty( $id) )
				$id = $this->lineNum;

			$nvp .= '<input type="hidden" name="item_name_' . $this->lineNum . '" value="'
				  . $descr . '" />';

			$nvp .= '<input type="hidden" name="item_number_' . $this->lineNum . '" value="'
				  . $id . '" />';

			$nvp .= '<input type="hidden" name="amount_' . $this->lineNum . '" value="'
				  . number_format( $this->cart->getPrice( $cid ) / 100, 2, '.', '' ) . '" />';

			$nvp .= '<input type="hidden" name="quantity_' . $this->lineNum . '" value="'
				  . $this->cart->getUnitsOfProduct( $cid ) . '" />'; 	// must be > 0

			$nvp .= '<input type="hidden" name="tax_' . $this->lineNum . '" value="'
				  . number_format( $this->cart->getTotalTaxAmountProduct($cid) /
				  				   $this->cart->getUnitsOfProduct($cid) /
				  				   100, 2, '.', '' )
				  . '" />';
			$nvp .= '<input type="hidden" name="shipping_' . $this->lineNum . '" value="0.00" />';
			
			$nvp .= '<input type="hidden" name="shipping2_' . $this->lineNum . '" value="0.00" />';

			++$this->lineNum;
		}

		$discount_amount = $this->cart->getSubTotalDiscountLines() * -1;
		if( $discount_amount != 0 ) {

			$nvp .= '<input type="hidden" name="discount_amount_cart" value="'
					  . number_format( $discount_amount / 100, 2, '.', '' ) . '" />';
		}
	}


	function addShippingHandlingToMessage ( &$nvp ) {

		$amount = $this->cart->getShippingHandlingTotal();

		// shouldn't add line items with cost 0
		if( $amount == 0 ) return;

		// ensure the description + option text is not longer than 127
		$descr = substr( _T("Shipment method: ") . $this->page->getExtraShipping( $this->page->getExtraShippingIndex() ), 0, 127);

		if( empty( $descr ) ) $descr = _T("Shipping and Handling");

		$nvp .= '<input type="hidden" name="item_name_' . $this->lineNum . '" value="'
			  . $descr . '" />';

		$nvp .= '<input type="hidden" name="item_number_' . $this->lineNum . '" value="'
			  . 'ship' . '" />';

		$nvp .= '<input type="hidden" name="amount_' . $this->lineNum . '" value="'
			  . number_format( $amount / 100, 2, '.', '' ) . '" />';

		$nvp .= '<input type="hidden" name="quantity_' . $this->lineNum . '" value="1" />';

		$nvp .= '<input type="hidden" name="tax_' . $this->lineNum . '" value="'
			 . $this->cart->getTaxAmountExtraShipping() . '" />';
		
		$nvp .= '<input type="hidden" name="shipping_' . $this->lineNum . '" value="0.00" />';
		
		$nvp .= '<input type="hidden" name="shipping2_' . $this->lineNum . '" value="0.00" />';
		
		++$this->lineNum;
	}


	function addTaxToMessage ( &$nvp ) {

		$nvp .= '<input type="hidden" name="tax_cart" value="'
			  . number_format( $this->cart->getTotalTax() / 100, 2, '.', '' )
		 	  . '" />';
	}



}

?>