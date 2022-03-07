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
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

// careful not to overwrite what earlier includes defined
global $myPageClassName;
if( empty( $myPageClassName  ) ) $myPageClassName  = 'ProPage';

// only after setting $myPageClassName it is safe to include other stuff 
require CARTREVISION . '/php/page.cls.php';

define( 'STOCKFILE', 'ccdata/store/solditems.ser.php' );
define( 'PROMOBASE', 'ccdata/datapro/data_promo.php' );

define( 'ORDERREFKEY', 'CartOrderRef' );
define( 'TRANSCODE', 'TransCode' );			// similar to ORDERREFKEY, but used for downloads


// include before assigning $products so array contains also the promo data
$handle = @fopen( PROMOBASE, 'r', 1 );
if( $handle ) {
	fclose( $handle );
	include PROMOBASE;
}


class ProPage extends Page {

	// properties
	var $promo = false;					// set by loadData if the include file exists
	var $fdb = false;					// data store acces
	var $discountTotal = false;			// cache for calculated discount
	var $sdrive = false;				// some pro features behave different in the sdrive environment
	
	// sync_marker from the data_stock.php file
	// if marker_in_soldItems < this_one then reset sold stock
	var $sync_marker = '';

	// array format: array( 'sold_items' => array(), 'sync_marker' => 0 );
	// in which the 'sold_items array is defined as: item['productid'] = value (qty)
	var $soldItemsData = array();


	// constructor
	function ProPage ( ) {

		$this->startSession();
		$this->loadData();
		$this->createCart();
		$this->isInSync();
		$this->lockCart( false );
		if( $this->getConfigS('track_stock') ) {
			$this->_loadSoldItems();
		}
	}


	// save only data in session because some php4 servers have problems
	// with loading serialized classes (reason unknown)
	function createCart ( ) {

		// only create a cart when it doesn't exist
		if( $this->cart ) 	return;

		$this->cart = new ShoppingCartPro( $this );
	}


	function emptyCart ( ) {

		$this->cart->emptyCart( true );

		// unset a possible reference to the transaction log
		// or multiple orders will all be written to the same location
		if( isset( $_SESSION[ ORDERREFKEY ] ) ) {
			unset( $_SESSION[ ORDERREFKEY ] );
		}
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


	// add some pro specific stuff
	function getConfig ( $param1, $param2 = false ) {

		switch ($param1) {

		case 'cc_search':
			if( $param2 === false )
				$result = 'searchproducts.php';
			else
				$result = 'searchproducts.php?keywords=' . urlencode( $param2 );
			break;

		case 'cc_paypaldirect':
			$result = "checkoutdirect.php";
			break;
		
		case 'cc_worldpay':
				$result = "checkoutwpay.php";
			break;

		case 'cc_2relay':
				$result = "relay2co.php";
			break;

		case 'paypaldirectimage':
			$result = 'ccdata/images/direct_payment.png';
			break;

		case 'twocoimage':
			$result = 'ccdata/images/2co.png';
			break;

		case 'worldpayimage':
			$result = 'ccdata/images/rbsworldpay.png';
			break;

		// next are the params determined by the application
		default:
			$result = parent::getConfig( $param1, $param2 );
		}

		return $result;
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


	function _formatProduct ( &$product ) {

		parent::_formatProduct( $product );
		
		// set the stock variable if needed
		if( $this->getConfigS( 'track_stock' ) ) {
			$product['stock'] = $this->getActualStockQty( $product['productid'] )						// actually sold
							  - $this->cart->getNumberOfOptionProducts( $product['productid'], -1 );	// in the cart
		}
		
		return $product;
	}


	function getDataMessage ( ) {

		if( $this->fdb ) return $this->fdb->GetErrorMessage();

		return false;
	}


	// return array of visible items unless the index is set
	function getExtraShipping ( $index = false ) {

		// check what method to use
		if( $this->getConfigS( 'shipping_calcmethod' ) == 'weight' ) {

			if( $index !== false ) {
				// return description of one item (which happens to be the same as the index in this case)
				return $index;
			}

			// copy items into same format as extra-shipping array
			$toshow = array();

			$rates =& $this->getConfigS( 'ShippingRates' );
			if( ! $rates ) return $toshow;

			$decrs = array_keys( current( $rates ) );

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


	function getCartGrandTotal ( ) {

		if( $this->discountTotal === false )
			$this->discountTotal = $this->cart->getPromoDiscount();  
		
		if( $this->discountTotal == -1 )
			return formatMoney( $this->cart->getGrandTotalCart(), 100);
		else
			return formatMoney( $this->cart->getGrandTotalCart() - $this->discountTotal, 100);
	}

	// return formatted discount or empty string if no promo module loaded
	function getCartDiscountTotal ( ) {
		
		if( $this->discountTotal === false )
			$this->discountTotal = $this->cart->getPromoDiscount(); 
			
		return $this->discountTotal == -1 ? '' : formatMoney( $this->discountTotal, 100 );
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


	/************** additional methods for fbase or database storage **************/

	// fields array MUST match between all users of FBase, so use this instead of 'new...'!
	// return false if no transaction logging configured or installed
	function createTransLogInstance ( ) {

		global $absPath;

		if( ! $this->getConfigS("transaction_log" ) ) return false;

		if( ! class_exists( 'FBase' ) ) {
			@include( 'fbase.cls.php' );		// surpress warning
			if( ! class_exists( 'FBase' ) ) return false;
		}

		// gatewayref is needed to handle callbacks from payment gateways
		return new FBase( $absPath . 'ccdata/store', array( 'lineitemcount', 'grandtotal', 'status', 'gatewayref' ) );
	}


	// return formatted transaction code or false if it doesn't exist
	function GetTransCode ( &$code ) {

		$code = false;

		if( $this->fdb === false ) $this->fdb = $this->createTransLogInstance();

		if( isset( $_SESSION[ ORDERREFKEY ] ) && $_SESSION[ ORDERREFKEY ] != '' ) {

			$code = $this->fdb->FormatRefField( $_SESSION[ ORDERREFKEY ] );
		}

		return $code;
	}


	/****************** additional methods for stock control *****************/

	/*
	 * Sold items data structure: array[productid][type] = value
	 * The 'type field can have 2 values:
	 * 		'client'	<- what the SCC client has received
	 * 		'cart' 		<- where sales are added
	 *
	 * When the client asks a update, 'cart' numbers are moved to 'client' and that data
	 * is send to the client.
	 *
	 * If a client refreshes more than once, counts are added to client.
	 *
	 * When the client syncs, 'client' is emptied.
	 *
	 * Sold numbers are the sum of the 2 values
	 */
	function _loadSoldItems ( ) {

		global $absPath;

		if( ! file_exists( $absPath . STOCKFILE ) ) return;

		$bytes = filesize( $absPath . STOCKFILE );
		if( $bytes == 0 ) return;

		$handle = fopen( $absPath . STOCKFILE, "r" );
		if( ! $handle ||
			! getFileLock( $handle, LOCK_SH ) ) return;

		$sdat = fread( $handle, $bytes );

    	flock( $handle, LOCK_UN);
		fclose( $handle );

		// remove the access denied part
		$pos = strpos( $sdat, '?>' );
		if( pos !== false ) {
			$sdat = substr( $sdat, $pos + 2 );
		}
		$this->soldItemsData = unserialize( $sdat );
	}


	// $route is the formatted form of the file location
	// only update existing item if $onlyIfExists is true
	// only update stock counters when $updateStock is true
	function saveTransactionData ( $data, &$route, $onlyIfExists = false, $updateStock = true ) {

		if( ! $this->getConfigS("transaction_log" ) ) {
			return true;								// not needed
		}

		if( $this->fdb === false ) {
			$this->fdb = $this->createTransLogInstance();
		}

		if( $this->fdb->StoreData( $data, $route, $onlyIfExists ) ) {

			// the transaction logging is working, so update the stock if needed
			if( $updateStock )
				$this->updateStockFromTransLog( $route );

			// and notify store owner
			$this->notifyOrder( $route );

			return true;
		}

		return false;
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

			$this->soldItemsData['sync_marker'] =$this->getConfigS('sync_marker');
		}

		$sdat = serialize( $this->soldItemsData );
		fwrite( $handle, '<?php echo "Access denied."; exit(0); ?>' . $sdat );

        flock( $handle, LOCK_UN);
		fclose( $handle );
	}


	function getStockQty( $productid ) {

		$p = & $this->getProduct ( $productid, false );

		if( $p && isset( $p['stock'] ) )		
			return $p['stock'];
		else
			return 0;
	}
	
	
	// if the scc stocks file is newer then the sold item file, ignore the 'client' data, those values
	// will be reset automatically at the next sale, see saveSoldItems().
	function getSoldItemQty ( $productid ) {

		if( isset( $this->soldItemsData['sold_items'][ $productid ] ) ) {
				$qty = $this->soldItemsData['sold_items'][ $productid ]['cart'];

				if( strcmp( $this->soldItemsData['sync_marker'], $this->getConfigS('sync_marker') ) == 0 ) {
					$qty += $this->soldItemsData['sold_items'][ $productid ]['client'];
				}

				return empty($qty) ? 0 : $qty;
		}

		return 0;
	}


	// returns the actually SOLD stocks, does not include what is in the cart
	function getActualStockQty ( $productid ) {

		$stock = (int) $this->getStockQty( $productid ) -
				 (int) $this->getSoldItemQty( $productid );

		// but is this enough to meet the quantity requirements for this product
		$prd =& $this->products[ $productid ];

		switch ( $prd['typequantity'] ) {

			case 'default_quantity':
				if( $stock < $prd['defaultquantity'] )
					$stock = 0;
				break;

			case 'range_quantity':
				if( $stock < $prd['minrangequantity'] )
					$stock = 0;
				break;
		}

		return $stock;
	}


	// make a report that SCC uses to update stock numbers
	function getSoldCountsForClient ( ) {

		global $absPath;

		if( ! file_exists( $absPath . STOCKFILE ) )
			return false;					// no data

		$handle = fopen( $absPath . STOCKFILE, 'r+');

		if( ! $handle ||
			! getFileLock( $handle, LOCK_EX ) )
			return false;					// can't access data

		$size = filesize( $absPath . STOCKFILE );

		if( $size > 0 ) {

			$sdat = fread( $handle, filesize( $absPath . STOCKFILE ) );
			rewind( $handle );

			if( ($pos = strpos( $sdat, '?>')) !== false ) {
				$sdat = substr( $sdat, $pos + 2 );
			}

			$this->soldItemsData = unserialize( $sdat );

			// some cleanup, such an entry shouldn't exist
			if( isset( $this->soldItemsData['sold_items'][''] ) ) {

				writeErrorLog( 'Stocks file maintenance, removed entry with empty groupid' );
				unset( $this->soldItemsData['sold_items'][''] );
			}

		} else {

	        flock( $handle, LOCK_UN);
			fclose( $handle );
			return false;					// no data, empty file
		}

		$counts = array();

		// check the sync marker.
		if( strcmp( $this->soldItemsData['sync_marker'], $this->getConfigS('sync_marker') ) != 0 ) {

			// outof sync, it means SCC uploaded a new file so ignore client part
			foreach ( $this->soldItemsData['sold_items'] as $pid => $pvalue )
			{
				$counts[$pid] = $this->soldItemsData['sold_items'][$pid]['cart'];
			}

		} else {

			// in sync, move cart numbers to client numbers and remember totals
			foreach( $this->soldItemsData['sold_items'] as $pid => $pvalue ) {

					// in PHP4 we can't modify $prdvalue
					$this->soldItemsData['sold_items'][$pid]['client'] += $pvalue['cart'];
					$this->soldItemsData['sold_items'][$pid]['cart'] = 0;

					$counts[$pid] = $this->soldItemsData['sold_items'][$pid]['client'];
			}
		}

		$sdat = serialize( $this->soldItemsData );
		fwrite( $handle, '<?php echo "Access denied."; exit(); ?>' . $sdat );
		flock( $handle, LOCK_UN);
		fclose( $handle );

		return $counts;
	}


	// make a report that SCC uses to update stock numbers
	// keep the file open between read and write to ensure data integrity
	function getStockCountsForClient ( ) {

		global $absPath;

		if( ! file_exists( $absPath . STOCKFILE ) )
			return false;					// no data

		$handle = fopen( $absPath . STOCKFILE, 'r+');

		if( ! $handle ||
			! getFileLock( $handle, LOCK_EX ) )
			return false;					// can't access data

		$size = filesize( $absPath . STOCKFILE );

		if( $size > 0 ) {

			$sdat = fread( $handle, filesize( $absPath . STOCKFILE ) );
			rewind( $handle );

			if( ($pos = strpos( $sdat, '?>')) !== false ) {
				$sdat = substr( $sdat, $pos + 2 );
			}

			$this->soldItemsData = unserialize( $sdat );

			// some cleanup, such an entry shouldn't exist
			if( isset( $this->soldItemsData['sold_items'][''] ) ) {
				writeErrorLog( 'Stocks file maintenance, removed entry with empty groupid' );
				unset( $this->soldItemsData['sold_items'][''] );
			}

		} else {
	        flock( $handle, LOCK_UN);
			fclose( $handle );
			return false;					// no data, empty file
		}

		$counts = array();

		// check the sync marker.
		if( strcmp( $this->soldItemsData['sync_marker'], $this->getConfigS('sync_marker') ) != 0 ) {

			// outof sync, it means SCC uploaded a new file so ignore client part
			foreach ( $this->soldItemsData['sold_items'] as $pid => $pvalue ) {

				$counts[$pid]['sold'] = $this->soldItemsData['sold_items'][$pid]['cart'];
			}

		} else {

			// in sync, move cart numbers to client numbers and remember totals
			foreach ( $this->soldItemsData['sold_items'] as $pid => $pvalue ) {

				// in PHP4 we can't modify $prdvalue
				$this->soldItemsData['sold_items'][$pid]['client'] += $pvalue['cart'];
				$this->soldItemsData['sold_items'][$pid]['cart'] = 0;

				$counts[$pid]['sold'] = $this->soldItemsData['sold_items'][$pid]['client'];
			}

			$sdat = serialize( $this->soldItemsData );
			fwrite( $handle, '<?php echo "Access denied."; exit(); ?>' . $sdat );
		}

		flock( $handle, LOCK_UN);
		fclose( $handle );

		// add stock values too
		foreach( $this->_getAllProducts() as $pid => $prd ) {
			if( ! isset( $counts[$pid] ) ) $counts[$pid]['sold'] = 0;
			$counts[$pid]['stock'] = $prd['stock'];
		}


		return $counts;
	}


	// returns an array of productid's that have <= 0 stock left
	function checkOutOfStock ( ) {

		$soldOut = false;

		foreach( $this->soldItemsData['sold_items'] as $prdid => $prdpvalue ) {

    		if( $this->getStockQty( $prdid ) <= $this->getSoldItemQty( $prdid ) ) {
    			$soldOut[] = $prdid;
    		}

		}

		return $soldOut;
	}


	function _getAllProducts ( ) {

		return $this->products;
	}

	/****************** additional methods for mail notifications *****************/

	function notifyOutOfStock ( ) {

		if( ! $this->getConfigS( 'track_stock' ) ||
			! $this->getConfigS( 'mail', 'send_outofstock' ) ) return;

		$body = $this->getFormattedOutOfStock();
		if( ! $body ) return;

		include_once 'mailer.cls.php';

		$mailer = new Mailer ( $this->getConfigS( 'mail', 'from_address' ) );
		$mailer->SetRecipients ( $this->getConfigS( 'mail', 'to_stock_address' ) );
		$mailer->SetSubject ( $this->getConfigS( 'mail', 'subject_outofstock' ) );
		$mailer->SetMessage( $body );
		$mailer->Send();
	}


	function notifyOrder ( $orderRoute ) {

		if( ! $this->getConfigS( 'transaction_log' ) ||
			! $this->getConfigS( 'mail', 'send_sales' ) ) return;

		include_once 'mailer.cls.php';

		$mailer = new Mailer ( $this->getConfigS( 'mail', 'from_address' ) );
		$mailer->SetRecipients ( $this->getConfigS( 'mail', 'to_sales_address' ) );
		$mailer->SetSubject ( $this->getConfigS( 'mail', 'subject_sales' ) . ' - ' . strtoupper( $orderRoute ) );
		$mailer->SetMessage( $this->getFormattedOrderDetail( $orderRoute ) );

		// double check message and set it to something if the previous failed
		if( empty( $mailer->message ) ) {
			$mailer->SetMessage( _T('A purchase has been made in your store.') );
		}

		$mailer->Send();
	}


	function getFormattedOrderDetail( $route ) {

		$orderDetail = '';
		$data = array();

		if( $this->fdb === false ) {
			$this->fdb = $this->createTransLogInstance();
		}

		if( $this->fdb->RetrieveData( $route, $data ) )
		{
			$myPage =& $this;		// alias this, because includes may refer to it

			// catch template in buffer and feed it to the mailer
			$msgfile = getLangIncludePath( 'transactionlogdetail.inc.php' );

			// fill some variables that are used by the template
			$output_type = 'message';
			$ourRef = strtoupper( $route );

			ob_start();						// output buffering
			include $msgfile;
			$orderDetail = ob_get_contents();
			ob_end_clean();

		}
		return $orderDetail;
	}

	
	// convert the outOfStock array to something readable, return false if not out-of-stock
	function getFormattedOutOfStock ( ) {

		$outOfStock = $this->checkOutOfStock();
		if( ! $outOfStock ) return false;
		
		$data = array();
		$textmsg = '';

		foreach( $outOfStock as $prdid ) {

			$prd =& $this->products[ $prdid ];

			$data[] = array( 'sku' => $prd['sku'],
			      			 'name' => $prd['name'],
						     'shortdesc' =>  $prd['shortdescription']
						   );

			// just in case the html message body can't be created
			$textmsg .= $prd['name'] . "\n";
		}

		// catch template in buffer
		$msgfile = $this->getLangIncludePath( 'outofstocknotice.inc.php' );

		ob_start();						// output buffering
		include $msgfile;
		$msg = ob_get_contents();
		ob_end_clean();
		
		// double check message and set it to something simple if the previous failed
		if( empty( $msg ) )
			$msg = _T('The following products ran out-of-stock: ') . "\n\n" . $textmsg;

		return $msg;
	}

}


?>
