<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Everything a normal page needs to know or do.....
*
* Some functions to manipulate the cart contents must be loaded
* statically in controller.php because PHP4 does not have dynamic methods
*
* Based on Page.cls.php for the base-version, but extended with stock control features:
*
*
* @version $Revision: 2990 $
* @author Cees de Gruijter
* @category SCC PRO
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (https://www.coffeecup.com/)
*/

define( 'DATAFILE', 'data/data.php' );
define( 'PAYMENT', 'PayResponse' );
define( 'DATABASE', 'ccdata/data/data.php' );


header('Content-Type: text/html; charset=utf-8');

// careful not to overwrite what earlier includes defined
global $myPageClassName;
if( empty( $myPageClassName ) ) $myPageClassName = 'Page';

class Page {

	function loadData ( ) {

		global $absPath;
		require DATAFILE;

		$this->products = $products;
		$this->groups = $groups;
		$this->starredproducts = $starredproducts;
		$this->categoryproducts = $categoryproducts;
		$this->extrashipping = $extrashippings;
		$this->creditcards = $creditcards;
		$this->pages = $pages;
		$this->config = $config;
		
		// TaxRate must exist to avoid errors in cart calculations
		if( ! isset( $this->config['TaxRates'] ) )
			$this->config['TaxRates'] = array();

			// see: https://en.wikipedia.org/wiki/Currency_sign
		$this->curSign = $this->getConfigS('currencysymbol');
	}

	// properties
	var $cart = false;
	var $config;
	var $curSign;
	var $products;
	var $groups = false;
	var $starredproducts = false;
	var $categoryproducts = false;
	var $message = '';					// markup for message in cart screen
	var $extrashipping = false;
	var $creditcards;
	var $pages = false;

	// constructor
	function Page ( ) {

		$this->startSession();
		$this->loadData();
		$this->createCart();
		$this->isInSync();
		$this->lockCart( false );
	}


	// save only data in session because some php4 servers have problems
	// with loading serialized classes (reason unknown)
	function createCart ( ) {

		// only create a cart when it doesn't exist
		if( $this->cart ) 	return;
		
		$this->cart = new ShoppingCart( $this );
		$_SESSION['ShopTimestamp'] = $this->getConfigS( 'timestamp' );
	}


	function emptyCart ( ) {

		$this->cart->emptyCart( true );

		// unset a possible reference to the transaction log
		// or multiple orders will all be written to the same location
		if( isset( $_SESSION[ ORDERREFKEY ] ) ) {
			unset( $_SESSION[ ORDERREFKEY ] );
		}
	}
	
	
	function saveCart ( ) {
		$this->cart->saveCart( );
	}


	function isInSync ( ) {

		if( isset( $_SESSION['ShopTimestamp'] ) && $this->getConfigS('timestamp') != $_SESSION['ShopTimestamp'] ) {
			
			if( $this->cart->getNumberOfLineItems() ) {
				$this->message = _T("The shop has just been updated. Please select the products that you want to buy again. Sorry for the inconvenience.");
			}
			$this->emptyCart();
			$_SESSION['ShopTimestamp'] = $this->getConfigS( 'timestamp' );
		}
	}


	function lockCart ( $lock = true ) {
		$this->cart->lock = $lock;
	}


	function getLockCart ( ) {
		#echo ($this->cart->lock ? 'locked' : 'free' );
		return $this->cart->lock;
	}


	function startSession ( ) {

		// session cookie name so multiple shops on a domain do not share a session
		// name may only contain alpanumeric characters and must have at least 1 character
		session_name( 'cc' . preg_replace('/\W/', '', $this->getConfigS( 'shopname' ) ) );
		session_start();
	}

	// template should call this to determine which checkout buttons to show
	// $method must match *exactly* what is in the data.php file!
	function hasCheckoutMethod ( $method ) {

		return $this->getConfigS( $method, 'enabled' );
	}


	function getGatewayName ( $gateway, $type = 'config' ) {

		static $gatewayname = array (
			'cc_googlecheckout' => array( 'user' => 'Google Checkout', 'config' => 'Google' ),
			'cc_paypalcheckout' => array( 'user' => 'PayPal Express', 'config' => 'PayPal' ),
			'cc_paypalwpscheckout' => array( 'user' => 'PayPal WPS', 'config' => 'PayPalWPS' ),
			'cc_anscheckout' => array( 'user' => 'Auth.Net', 'config' => 'AuthorizeNetSIM' ),
			'cc_2checkout' => array( 'user' => '2CO', 'config' => '2CO' ),
			'cc_worldpay' => array( 'user' =>'WorldPay', 'config' => 'WorldPay' )
			);

		if( isset( $gatewayname[ $gateway ] ) ) {
			return $gatewayname[ $gateway ][ $type ];
		}
		return $gateway;
	}


	function getPaymentState ( ) {

		if( $this->cart->getNumberOfLineItems() == 0 ) {
			return -1;						// empty cart, no payment possible
		} else if( !isset($this->resArray) ||
				   !isset($this->resArray['TOKEN']) ||
				   $this->resArray['TOKEN'] == '' ) {
			return 0;						// no payment in progress
		} else if( isset($this->resArray['PAYMENTINFO_0_PAYMENTSTATUS']) &&
			$this->resArray['PAYMENTINFO_0_PAYMENTSTATUS'] == 'Completed') {
			return 2;						// payment completed
		} else {
			return 1;						// connection with PayPal established
		}
	}

	// concise version of getConfig() that doesn't die, but returns false if something isn't found
	function getConfigS ( $param1, $param2 = false ) {

		if( ! isset($this->config[$param1]) )
			return false;								// param1 doesn't exist

		if( $param2 ) {
			if( isset($this->config[$param1][$param2]) )
				return $this->config[$param1][$param2];
			else
				return false;							// param2 is asked for, but doesn't exist
		}

		return $this->config[$param1];
	}


	// long version of getConfig() that dies when a setting doesn't exist
	function getConfig ( $param1, $param2 = false ) {

		switch ($param1) {

			// these are params determined by php
		case 'cc_search':
			if( $param2 === false )
				$result = 'searchproducts.php';
			else
				$result = 'searchproducts.php?keywords=' . urlencode( $param2 );
			break;

		case 'cc_paypalcheckout':
			if( $param2 ) 					// asked url only
				$result = "checkoutpp.php";
			else
				$result = "checkoutpp.php?updateinfo";
			break;

		case 'cc_paypalwpscheckout':
			$result = "checkoutpps.php";
			break;

		case 'cc_paypaldirect':
			$result = "checkoutdirect.php";
			break;
		
			// these are params determined by php
		case 'cc_googlecheckout':
			if( $param2 ) 					// asked url only
				$result = "checkoutgc.php";
			else
				$result = "checkoutgc.php?updateinfo";
			break;

		case 'cc_anscheckout':
				$result = "checkoutans.php";
			break;

		case 'cc_2checkout':
				$result = "checkout2co.php";
			break;

		case 'cc_worldpay':
				$result = "checkoutwpay.php";
			break;

		case 'cc_2relay':
				$result = "relay2co.php";
			break;

		case 'paypalimage':
			$result = 'https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif';
			break;

		case 'paypaldirectimage':
			$result = 'ccdata/images/direct_payment.png';
			break;

		case 'paypalwpsimage':
			$result = 'https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif';
			break;

		case 'googleimage':
			$result = 'https://checkout.google.com/buttons/checkout.gif?merchant_id='
					. $this->getConfigS( 'Google', 'merchant_id' )
					. '&w=160&h=43&style=trans&variant=text&loc=en_US';
			break;

		case 'authorizeimage':
			$result = 'ccdata/images/authnet_checkout.gif';
			break;

		case 'twocoimage':
			$result = 'ccdata/images/2co.png';
			break;

		case 'worldpayimage':
			$result = 'ccdata/images/rbsworldpay.png';
			break;

		case 'cc_confirmpayment':
			if( $param2 ) 					// asked url only
				$result = "checkoutpp.php";
			else
				$result = "checkoutpp.php?payment');";
			break;

		default:
			$result = $this->getConfigS( $param1, $param2 );
		}

		return $result;
	}


	// return array of product id's
	function getStarredProducts ( $groupid = false ) {

		// use === because groupid can have value 0
		if( $groupid === false ) return $this->starredproducts;

		$prods = array();

		foreach( $this->starredproducts as $prod ) {
			if( $prod['groupid'] == $groupid ) $prods[] = $prod;
		}
		return $prods;
	}


	// format returned  array ( 1, 3, 4 )
	function getStarredGroups ( ) {

		$grps = array();

		// If there are no starred products, may be the user wants only categories to be shown
		if( count( $this->starredproducts ) != 0 )
		{
			foreach( $this->starredproducts as $prod ) {

			// We take its parent to be sure always is shown
				if( $this->groups[ $prod['groupid'] ][ 'parentid' ] != -1 )
					$grps[ $this->groups[ $prod['groupid'] ]['parentid'] ] = 1;

				// add to a map to catch duplicates
				$grps[ $prod['groupid'] ] = 1;
			}
		}
		else
		{
			foreach( $this->groups as $group ){
				$grps[ $group['groupid'] ] = 1;
			}
		}
		return array_keys($grps);
	}
	


	// format returned  array ( array('productid' => '7', 'groupid' => '0'), ...
	function getCategoryProducts ( $groupid = false ) {

		// use === because groupid can have value 0
		if( $groupid === false ) return $this->categoryproducts;

		$prods = array();
		foreach( $this->categoryproducts as $prod ) {
			if( $prod['groupid'] == $groupid ) $prods[] = $prod;
		}
		return $prods;
	}


	// format returned  array ( 1, 3, 4 )
	function getCategoryGroups ( ) {

		$grps = array();
		// In case that there are no starred products, maybe the user wants only categories to be shown
		if( count( $this->categoryproducts ) != 0 && $this->getConfigS('showcategoryproducts') == '1' )
		{
			foreach( $this->categoryproducts as $prod ) {
				// We take its parent to be sure always is shown
				if( $this->groups[ $prod['groupid'] ][ 'parentid' ] != -1 )
					$grps[ $this->groups[ $prod['groupid'] ]['parentid'] ] = 1;

				// add to a map to catch duplicates, and its parent if needed
				$grps[ $prod['groupid'] ] = 1;
			}
		}
		else
		{
			foreach( $this->groups as $group ){
				$grps[ $group['groupid'] ] = 1;
			}
		}
		return array_keys($grps);
	}


	function getCartProducts ( ) {
		return $this->cart->getPairProductIdGroupId();
	}


	function getProductNameByCartId ( $cartid ) {
		return $this->cart->Prods[$cartid]['name'];
	}


	function getGroups ( ) {
		// groups can be empty
		if( ! isset( $this->groups ) || ! is_array( $this->groups ) )
			return array();

		return $this->groups;
	}


	function getSubGroups( $id = '-1' )
	{
		if( $id == '-1' )
		{
			return array();
		}

		$grps = array();
		// If there are no subgroups or the data is not set, return empty array.
		if( ! isset( $this->groups[$id]['subgroupsIds'] ) || ! is_array( $this->groups[$id]['subgroupsIds'] ) )
			return array();

		if( count( $this->groups[$id]['subgroupsIds'] ) != 0 )
		{
			foreach( $this->groups[$id]['subgroupsIds'] as $grpId ) {
				// add to the map of the subgroups
				$grps[ $grpId ] =  $this->groups[$grpId];
			}
		}

		return $grps;
	}


	function getCreditCards ( ) {

		// credit cards can be empty
		if( ! isset( $this->creditcards ) || ! is_array( $this->creditcards ) )
			return array();

		return $this->creditcards;
	}


	function getTaxLocations ( ) {
		return $this->getConfigS('TaxLocations');
	}


	function getPages ( $pagetype = '' ) {

		if( ! isset( $this->pages ) || ! is_array( $this->pages ) )
			return array();

		// If the string is empty we return the full array
		if( empty( $pagetype ) ) {
			return $this->pages;
		}

		// In case we want a determined page type, we need a auxiliar vector
		// to fill (prevent issue foreach nested loops on PHP4)
		$param_pages = array();

		foreach( $this->pages as $key=>$curr_page ) {

			if( $curr_page['type'] == $pagetype ) {
				$param_pages[$key] = $curr_page;
			}
		}

		return $param_pages;
	}


	function getPage ( $pageid ) {

		if( ! isset( $this->pages[$pageid] ) || ! is_array( $this->pages ) )
			return array();

		return $this->pages[$pageid];
	}


	function existsGroup( $groupid ) {

		return isset( $this->groups[ $groupid ] );

	}


	function getGroup ( $groupid ) {

		// groups can be empty
		if( ! isset( $this->groups ) ||
			! isset( $this->groups[$groupid] ) )
		{
			return array();
		}

		return $this->groups[$groupid];
	}


	// array with all products, no formating done on price fields
	// this doesn't make a whole lot of sense if we have a database
	// see what happens if we remove this method
//	function getProducts ( ) {
//		return $this->products;
//	}


	// array with products in a group, no formating done on price fields
	function getProductsByGroup ( $groupid ) {

		$result = array();

		foreach( $this->groups[ $groupid ]['productsIds'] as $prdid ) {

			$result[ $prdid ] =& $this->products[$prdid];
		}
		return $result;
	}


	// note that the base version needs to start using the unique product ids too
	function existsProduct( $productid ) {

		return isset( $this->products[ $productid ] );
	}


	// product with correctly formatted fields.
	// use data from cart if $_GET['cartid'] is set and quantity from $_POST.
	function getProduct ( $productid, $formated = true ) {

		if( ! isset( $this->products[ $productid ] ) )
			return false;

		if( $formated ) {

			// copy and format product data
			$product = $this->products[ $productid ];
			return $this->_formatProduct ( $product );

		} else {

			// return the raw data
			return $this->products[ $productid ];
		}
	}


	function _formatProduct ( &$product ) {

		// add formating to the copy
		$product['yourprice'] = formatMoney($product['yourprice'], 100);
		$product['retailprice'] = formatMoney($product['retailprice'], 100);
		$product['tax'] = formatMoney($product['tax'], 100);
		$product['shipping'] = formatMoney($product['shipping'], 100);
		$product['handling'] = formatMoney($product['handling'], 100);
		$product['weight'] = formatAmount( (float) $product['weight'] / pow(10 ,$product['weightdigits'] ), $product['weightdigits'] );
		$product['quantity'] = $product['defaultquantity'];

		if( $product['ispercentage'] == 0)
			$product['discount'] = formatMoney($product['discount'], 100);
		else
			$product['discount'] = formatAmount($product['discount'] / 100, 2);

		if( isset( $_GET['cartid'] ) ) {

			$product['cartid'] = $_GET['cartid'];
			$product['cart_optionIds'] = $this->cart->getOptions($_GET['cartid']);
			$product['cart_qty'] = $this->cart->getUnitsOfProduct($_GET['cartid']);

			if( ( $optionIds = $this->cart->getOptions( $_GET['cartid']) ) != '' ) {

				// update 'selected' info of options array with cart data
				$ids = explode(',', $optionIds);

				for( $i = 0; $i < count($ids); ++$i ) {

					if( $ids[$i] > 0 ) {

						// there is an option defined, let's find it
						for( $oi = 0; $oi < count($product['options'][$i]['items']); ++$oi) {

							$item =& $product['options'][$i]['items'][$oi];

							if( $item['value'] == $ids[$i] ) {
								$item['selected'] = 1;
								break;		// because there can only be 1 selected per option
							}
						}
					}
				}
			}
		} else if( isset($_POST['quantity']) && $_POST['quantity'] > 0 ) {
			// for when the user is in the details screen, clicks submit and the page is reloaded
			$product['quantity'] = $_POST['quantity'];
		}

		return $product;
	}


	// sets the contents of the message
	function setCartMessage ( $text = '' ) {
		$this->message = $text;
	}


	// return the contents of the message or false if no message exists
	function getCartMessage ( ) {

		if( trim( $this->message ) == '' ) return false;

		return $this->message;
	}


	function getDataMessage ( ) {

		if( $this->fdb ) return $this->fdb->GetErrorMessage();

		return false;
	}


	function getDateProductBase ( ) {
		return $this->getConfigS('timestamp');
	}


	// return array of visible items unless the index is set
	function getExtraShipping ( $index = false ) {

		// check what method to use
		if( $this->getConfigS( 'shipping_calcmethod' ) == 'weight' ) {

			$rates =& $this->getConfigS( 'ShippingRates' );
			$decrs = array_keys( current( $rates ) );

			if( $index !== false ) {
				// return description of one item (which happens to be the same as the index in this case)
				return $index;
			}

			// copy items into same format as extra-shipping array
			$toshow = array();
			foreach( $decrs as $descr ) {
					$toshow[] = array( 'id' => $descr, 'description' => $descr );
			}

			return $toshow;

		}

		if( $index === false ) {

			if( isset( $this->extrashipping ) && is_array( $this->extrashipping )) {

				// copy the visible items
				$toshow = array();

				foreach( $this->extrashipping as $es ) {

					if( $es['show'] ) {
						$toshow[] = $es;
					}
				}

				return $toshow;

			} else {

				// no data defined
				return false;

			}

		} else {

			// find the item we need
			foreach( $this->extrashipping as $es ) {

				if( $es['id'] == $index ) {
					return $es['description'];
				}
			}
		}

	}


	function getExtraShippingIndex ( ) {
		return $this->cart->getExtraShippingIndex();
	}


	function getCartSubtotalPriceProduct ( $cartid ) {
		return formatMoney($this->cart->getSubtotalPriceProduct($cartid), 100 );
	}


	function getCartUnitsOfProduct ( $cartid ) {
		return $this->cart->getUnitsOfProduct( $cartid );
	}


	function getCartGrandTotal ( ) {

		if( $this->discountTotal === false )
			$this->discountTotal = $this->cart->getPromoDiscount();  
		
		if( $this->discountTotal == -1 )
			return formatMoney( $this->cart->getGrandTotalCart(), 100);
		else
			return formatMoney( $this->cart->getGrandTotalCart() - $this->discountTotal, 100);
	}


	function getCartTaxTotal ( ) {
		return formatMoney($this->cart->getTotalTax(), 100);
	}


	function getCartSubTotal ( ) {
		return formatMoney($this->cart->getSubtotalPriceCart(), 100);
	}


	function getCartShippingTotal ( ) {
		return formatMoney($this->cart->getTotalShipping(), 100);
	}

	// return formatted discount or empty string if no promo module loaded
	function getCartDiscountTotal ( ) {
		
		if( $this->discountTotal === false )
			$this->discountTotal = $this->cart->getPromoDiscount(); 
			
		return $this->discountTotal == -1 ? '' : formatMoney( $this->discountTotal, 100 );
	}

	function getCartHandlingTotal ( ) {
		return formatMoney($this->cart->getTotalHandling(), 100);
	}


	function getCartShippingHandlingTotal ( ) {

		$amount = $this->cart->getShippingHandlingTotal();

		if( $amount !== false )
			return formatMoney( ($this->cart->getShippingHandlingTotal() ), 100);

		// In case no valid shipping is available we use a zero price value 
		return formatMoney( 0, 100 );
	}


	function getCartCount ( ) {
		return $this->cart->getNumberOfLineItems();
	}


	// return false if out-of-stock, set $prod_descr to first item that fails
	// PHP4 caused problems when this method was located in the cart object.
	function verifyAvailability ( &$prod_descr ) {

	 	foreach( $this->cart->Prods as $cid => $lineitem ) {

	 		// don't use qty field - it doesn't take into a account that a product may appear
	 		// in more than 1 lineitem with different options
	 		if( $this->cart->getNumberOfProducts( $lineitem['productid'] ) >
	 			$this->getActualStockQty( $lineitem['productid'] ) )
	 		{
	 			$prod_descr = $lineitem['name'];
	 			return false;
 			}
	 	}
		return true;
	}



	// return true for 'enabled' and echo the text (if any) for each state
	function setConnectButtonState ( $echofalse = '', $echotrue = '' ) {

		switch( $this->getPaymentState() ) {
		case -1:		// empty cart
		case 2:			// paid and done
		case 1:			// token from PP recieved
			$result = false;
			break;
		case 0:			// payment not started
			$result = true;
			break;
		}
		if( $result ) echo $echotrue;
		else echo $echofalse;
		return $result;
	}


	// return true for 'enabled' and echo the text (if any) for eache state
	function setPayButtonState ( $echofalse = '', $echotrue = '' ) {

		switch( $this->getPaymentState() ) {

		case -1:		// empty cart
		case 2:			// paid and done
		case 0:			// token from PP recieved
			$result = false;
			break;

		case 1:			// payment not started
			$result = true;
			break;
		}
		if( $result ) echo $echotrue;
		else echo $echofalse;
		return $result;
	}


	// used to return stock from failed transaction to inventory
	// return true upon success
	function revertStockFromTransaction ( $route ) {

		if( ! $this->getConfigS( 'track_stock' ) ) false;

		if( $this->fdb === false ) {
			$this->fdb = $this->createTransLogInstance();
		}

		$prod_data = array();
		if( ! $this->fdb->RetrieveData( $route, $prod_data ) ) {

			writeErrorLog( 'Could not update SoldItems because FDB::RetrieveData failed.' );
			return false ;
		}

		// ensure we only revert stock once
		if( isset( $prod_data[ FB_STOCKUPDATED ] ) &&
			$prod_data[ FB_STOCKUPDATED] &&
			! isset( $prod_data[ FB_STOCKRETURNED ] ) &&
			! $prod_data[ FB_STOCKRETURNED] )
		{
			$this->saveSoldItems( $prod_data['lines'], true );
			$this->fdb->StoreData( array( FB_STOCKRETURNED => 1 ), $route, true );
			return true;
		}
		return false;
	}


	// removes entires from the transaction log database
	// return true upon success
	function delTransaction ( $route ) {

		if( $this->fdb === false ) {
			$this->fdb = $this->createTransLogInstance();
		}

		return $this->fdb->RemoveData( $route );
	}


	// called just before or just after (config) a sales transaction
	function updateStockFromTransLog ( $route ) {

		if( ! $this->getConfigS( 'track_stock' ) ) return;

		if( $this->fdb === false ) {
			$this->fdb = $this->createTransLogInstance();
		}

		$prod_data = array();
		if( ! $this->fdb->RetrieveData( $route, $prod_data ) ) {

			writeErrorLog( 'Could not update SoldItems because FDB::RetrieveData failed.' );
			return;
		}

		// ensure we only update stock once
		if( ! isset( $prod_data[ FB_STOCKUPDATED ] ) ||
			! $prod_data[ FB_STOCKUPDATED] )
		{
			$this->saveSoldItems( $prod_data['lines'] );
			$this->fdb->StoreData( array( FB_STOCKUPDATED => 1 ), $route, true );

			$this->notifyOutOfStock();
		}
	}


	// When $revert is true, items are ADDED BACK to the stock instead of discounted.
	function saveSoldItems ( &$sales, $revert = false ) {

		global $absPath;

		if( empty( $sales ) ) return;		// nothing to do

		if( file_exists( $absPath . STOCKFILE ) )
			$mode = 'r+';	// read and write
		else
			$mode = 'w';	// create new empty file

		$handle = fopen( $absPath . STOCKFILE, $mode);
		if( ! $handle ||
			! getFileLock( $handle, LOCK_EX ) ) return;

		if( $mode == 'w' ) {

			// initialize the array for a new file
			$this->soldItemsData = array( 'sold_items' => array(), 'sync_marker' => $this->getConfigS('sync_marker') );

		} else {

			// read the data first, it may have been changed
			$size = filesize( $absPath . STOCKFILE );

			if( $size > 0 ) {

				$sdat = fread( $handle, filesize( $absPath . STOCKFILE ) );
				rewind( $handle );

				if( ($pos = strpos( $sdat, '?>')) !== false ) {
					$sdat = substr( $sdat, $pos + 2 );
				}

				$this->soldItemsData = unserialize( $sdat );

			} else {
				// sync_marker is used to notice if data_stock.php has been updated
				$this->soldItemsData = array( 'sold_items' => array(), 'sync_marker' => $this->getConfigS('sync_marker') );
			}
		}

   		// data format of $sales is similar as that from cart data (but fewer fields)
   		foreach( $sales as $cid => $item ) {

			// every item must have an id
			if( ! isset( $item['productid'] ) || $item['productid'] == '' ) {
				writeErrorLog( 'Trying to update Stocks with an item without ID: ' . implode( $item, ',' ) );
				continue;
			}

			if( $revert )
				$qty = -1 * $item['qty'];
			else
				$qty = $item['qty'];

   			if( isset( $this->soldItemsData['sold_items'][ $item['productid'] ] ) )
   			{
   				// update existing record
	    		$this->soldItemsData['sold_items'][ $item['productid'] ]['cart'] += $qty;
   			} else {
				// add new record
	    		$this->soldItemsData['sold_items'][ $item['productid'] ]['cart'] = $qty;
	    		$this->soldItemsData['sold_items'][ $item['productid'] ]['client'] = 0;
   			}
    	}

		// Check if SCC has updated the stocks file, if so then reset client counters
		//          sold items marker                    stocks file marker
		if( strcmp( $this->soldItemsData['sync_marker'], $this->getConfigS('sync_marker') ) != 0 ) {

			foreach( $this->soldItemsData['sold_items'] as $pid => $pvalue ) {
				$this->soldItemsData['sold_items'][ $pid ]['client'] = 0;
			}

			$this->soldItemsData['sync_marker'] = $this->getConfigS('sync_marker');
		}

		$sdat = serialize( $this->soldItemsData );
		fwrite( $handle, '<?php echo "Access denied."; exit(0); ?>' . $sdat );

        flock( $handle, LOCK_UN);
		fclose( $handle );
	}


}


?>
