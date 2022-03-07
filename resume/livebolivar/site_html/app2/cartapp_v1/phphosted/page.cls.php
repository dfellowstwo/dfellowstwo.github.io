<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Overwrite methods in php/page.cls.php to work with the database
*
* @author Cees de Gruijter
* @category SCC Hosted
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (https://www.coffeecup.com/)
*/

// careful not to overwrite what earlier includes defined
global $myPageClassName;
if( empty( $myPageClassName  ) ) $myPageClassName  = 'HostedPage';

// only after setting $myPageClassName it is safe to include other stuff 
require 'phppro/page.cls.php';

//require 'database_mysql.cls.php';
require 'database_sqlite.cls.php';


// The data structure for sold items differs between Pro and Hosted version,
// due to the difference between how the data is stored.
// 		Pro:		$this->soldItemsData['sold_items']['productid']['cart']
// 					$this->soldItemsData['sold_items']['productid']['client']
// 					$this->soldItemsData['sync_marker']
// 		Hosted:		$this->soldItemsData['productid']['cart']
// 					$this->soldItemsData['productid']['client']
//					$this->soldsyncmarker = '';

class HostedPage extends ProPage {

	var $db;
	var $soldsyncmarker = '';
	
	function __construct ( ) {

		$this->loadData();
		$this->startSession();
		$this->createCart();
		$this->isInSync();
		$this->lockCart( false );

		if( $this->getConfigS('track_stock') ) {
			$this->_loadSoldItems();
		}
	}

	// don't load data, but set up the database connection
	function loadData ( ) {

		$this->db = new Database( 'readDB' );

		$this->_getGroups();
		$this->_getStarredProducts();
		$this->_getCategoryProducts();
		$this->_getPages();
		$this->_getExtraShipping();
		$this->_loadSoldItems();
	}


	function getConfigS ( $param1, $param2 = false ) {

		static $sth = false;

		if( ! $sth ) {
			$sql = "SELECT type," .
				   "   CASE WHEN type='bool' THEN shortval " .
				   "   WHEN type='array' OR type ='longval' OR type ='json' THEN longval" .
				   "   ELSE shortval END AS value" .
				   " FROM " . TCONFIG . " WHERE id = :id" ;
			$sth = $this->db->prepare( $sql, array( PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ) );
		}

		// build a key
		$key = $param1 . ( $param2 ? ':' . $param2 : '');
		$sth->execute( array( ':id' => $key ) );
		$data = $sth->fetch( PDO::FETCH_ASSOC );

		if( $data === false || empty( $data ) ) return false;			// no data for this key

		// format the return value based on type
		switch( $data['type'] ) {

			case 'bool':
				return $data['value'] ? true : false;

			case 'array':
				return unserialize( $data['value'] );

			case 'json':
			return json_decode( $data['value'] , true );
					
			default:
				return $data['value'];
		}
	}
	
	// this is in the db in unsorted json format 
	function getTaxLocations ( ) {
		$data =& $this->getConfigS( 'TaxLocations' );
		// sort on numerical value of key
		ksort( $data, SORT_NUMERIC );
		return $data;
	}
	


	function existsProduct( $productid ) {

		static $sth = false;

		if( ! $sth ) {
			$sql = "SELECT count(*) " .
				   " FROM " . TPRODUCT . " WHERE productid = :productid" ;
			$sth = $this->db->prepare( $sql, array( PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ) );
		}

		$sth->execute( array( ':productid' => $productid ) );

		return $sth->fetchColumn() != 0 ;

	}


	// array with products in a group, no formating done on price fields
	function getProductsByGroup ( $groupid ) {

		static $sth = false;

		if( ! $sth ) {
			$sql = "SELECT *" .
				   " FROM " . TPRODUCT . " WHERE groupid = :groupid" ;
			$sth = $this->db->prepare( $sql, array( PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ) );
		}

		$sth->execute( array( ':groupid' => $groupid ) );

		$result = array();

		foreach( $sth as $row ) {

			if( ord( $row['object'] ) == 123 )					// 123 is ascii value for {
				$result[ $row['productid'] ] = json_decode( $row['object'], true );
			else
				$result[ $row['productid'] ] = unserialize( $row['object'] );

		}

		return $result;
	}


	function getProduct ( $productid, $formated = true ) {

		static $sth = false;

		if( ! $sth ) {
			$sql = "SELECT *" .
				   " FROM " . TPRODUCT . " WHERE productid = :productid" ;
			$sth = $this->db->prepare( $sql, array( PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ) );
		}

		$sth->execute( array( ':productid' => $productid ) );
		$data = $sth->fetch( PDO::FETCH_ASSOC );

		if( $data === false ) return false;					// product not found

		// object is either serialized or jsonized
		if( ord( $data['object'] ) == 123 )					// 123 is ascii value for {
			$product = json_decode( $data['object'], true );
		else
			$product = unserialize( $data['object'] );

		if( $formated ) {
			return $this->_formatProduct ( $product );
		} else {
			return $product;
		}
	}
	
	
	function getCreditCards ( ) {

		// credit cards can be empty
		if( ! isset( $this->creditcards ) || ! is_array( $this->creditcards ) ) {
			$sql = 'SELECT * FROM ' . TCREDITCARD;
			$this->creditcards = $this->db->fetchAll( $sql );
		}

		return $this->creditcards;
	}
	

	// same as the base version + digital download extension
	function saveTransactionData ( $data, &$route, $onlyIfExists = false, $updateStock = true ) {

		if( ! parent::saveTransactionData ( $data, &$route, $onlyIfExists, $updateStock ) ) {
			return false;
		}

		// update digital downloads if needed
		if(	$this->getConfigS( 'digital_downloads', 'enabled' ) &&
			method_exists( $this, 'AddDownload') )						// safety net
		{
			// get the order details first, the $data we got contains only a few fields
			if( ! $this->fdb->RetrieveData( $route, $data ) ||
				! isset( $data['gatewayref'] ) ||
				! $this->AllowDownload( $data['gateway'], $data['status'] ) )
			{
				// no data or no gateway reference or no confirmation from gateway yet
				writeErrorLog( 'No order details found for route: ' . $route . ' (' . $this->getDataMessage() . ')', $data );
				return true;
			}
			$this->AddDownload( $route, $data );

		}

		return true;
	}


	/*	Location of data:
	 *		With FBase					  	 	With SQLite Database
	 *		$this->availableStock       	 => part of the products in readDB
	 *		$this->getConfigS('sync_marker') => TCONFIG table in readDB, key FSCCSYNC, column 'shortval'
	 *		our copy of the sync_marker		 => TCONFIG table in writeDB, key FCARTSYNC, column 'shortval'
	 *		$this->soldItemsData     		 => TSTOCK table, columns 'cart' and 'client'
	 *
	 *	When $revert is true, items are ADDED BACK to the stock instead of discounted.
	 */
	function saveSoldItems ( &$sales, $revert = false ) {
		
		$writeDB = new Database( 'writeDB' );

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

			if( $writeDB->existsValue( TSTOCK, 'productid', $item['productid'] ) )
   			{
				$sql = 'UPDATE ' . TSTOCK . ' SET cart = cart + :cart WHERE productid = :productid;';
   			} else {
				$sql = 'INSERT INTO ' . TSTOCK . '( productid, cart, client ) VALUES (:productid, :cart, 0);';
   			}

			$sth = $writeDB->prepare( $sql );
			$sth->bindParam(':productid', $item['productid'], PDO::PARAM_INT);
			$sth->bindParam(':cart', $qty, PDO::PARAM_INT);
			$sth->execute();
    	}

		// Check if SCC has updated the stocks file, if so then reset client counters
		$scc = $this->getConfigS( FSCCSYNC );
		$sth = $writeDB->query( 'SELECT shortval FROM ' . TCONFIG . ' WHERE id=\'' . FCARTSYNC . '\';' );  
		if( $sth === false )		$shp = '';						// no marker found
		else						$shp = $sth->fetchColumn();

		if( $scc != $shp ) {

			// set the client counters to 0 and set the cart sync marker to the scc marker
			$sql = 'UPDATE ' . TSTOCK . ' SET client=0;'
				 . 'UPDATE ' . TCONFIG . ' SET shortval=\'' . $scc . '\' WHERE id=\'' . FCARTSYNC . '\';'; 
			$writeDB->exec( $sql );

			$this->soldItemsData['sync_marker'] = $this->getConfigS('sync_marker');
		}

	}

	// if the scc stocks file is newer then the sold item file, ignore the 'client' data, those values
	// will be reset automatically at the next sale, see saveSoldItems().
	function getSoldItemQty ( $productid ) {

		if( isset( $this->soldItemsData[ $productid ] ) ) {
				$qty = $this->soldItemsData[ $productid ]['cart'];

				if( strcmp( $this->soldsyncmarker, $this->getConfigS( FSCCSYNC ) ) == 0 ) {
					$qty += $this->soldItemsData['sold_items'][ $productid ]['client'];
				}

				return empty($qty) ? 0 : $qty;
		}

		return 0;
	}


	// make a report that SCC uses to update stock numbers
	function getStockCountsForClient ( ) {

		$counts = array();

		// check the sync marker.
		if( $scc = $this->getConfigS( FSCCSYNC ) != $this->soldsyncmarker ) {

			// outof sync, it means SCC uploaded a new file so ignore client part
			foreach ( $this->soldItemsData as $pid => $pvalue ) {

				$counts[$pid]['sold'] = $this->soldItemsData[$pid]['cart'];
			}

		} else {

			// in sync, move cart numbers to client numbers and remember totals
			foreach ( $this->soldItemsData as $pid => $pvalue ) {

				// in PHP4 we can't modify $pvalue
				$this->soldItemsData[$pid]['client'] += $pvalue['cart'];
				$this->soldItemsData[$pid]['cart'] = 0;

				$counts[$pid]['sold'] = $this->soldItemsData[$pid]['client'];
			}
			
			$this->_saveSoldItems();

		}

		// add stock values too
		foreach( $this->_getAllProducts() as $pid => $prd ) {
			if( ! isset( $counts[$pid] ) ) $counts[$pid]['sold'] = 0;
			$counts[$pid]['stock'] = $prd['stock'];
		}

		return $counts;
	}


	/************** additional methods for fbase or database storage **************/

	// fields array MUST match between all users of FBase, so use this instead of 'new...'!
	// return false if no transaction logging configured or installed
	function createTransLogInstance ( ) {

		if( ! $this->getConfigS("transaction_log" ) ) return false;

		if( ! class_exists( 'HostedFBase' ) ) {
			include( 'fbase.cls.php' );		// surpress warning
			if( ! class_exists( 'HostedFBase' ) ) return false;
		}

		return new HostedFBase( TTRANS );
	}


	/****************** additional methods for mail notifications *****************/

	// only differs from the ProPage::notifyOutOfStock in where the config data comes from
	function notifyOutOfStock ( ) {

		if( ! $myPage->sdrive ) {
			ProPage::notifyOutOfStock();
			return;
		}
		
		if( ! $this->sdrive['sdrive_account_shop_notify_outofstock'] ) return;

		$body = $this->getFormattedOutOfStock();
		if( ! $body ) return;

		include_once 'mailer.cls.php';

		$mailer = new Mailer( $this->sdrive['sdrive_account_shop_from_address'] );
		$mailer->SetRecipients( $this->sdrive['sdrive_account_shop_outofstock_emails_addresses'] );
		$mailer->SetSubject ( $this->sdrive['sdrive_account_shop_outofstock_emails_subject'] );
		$mailer->SetMessage( $body );
		$mailer->Send();
	}


	// only differs from the ProPage::notifyOrder in where the config data comes from
	function notifyOrder ( $orderRoute ) {

		if( ! $myPage->sdrive ) {
			ProPage::notifyOrder( $orderRoute );
			return;
		}
		
		if( ! $this->sdrive['sdrive_account_shop_notify_transactions'] ) return;

		include_once 'mailer.cls.php';

		$mailer = new Mailer( $this->sdrive['sdrive_account_shop_from_address'] );
		$mailer->SetRecipients( $this->sdrive['sdrive_account_shop_transaction_emails_addresses'] );
		$mailer->SetSubject ( $this->sdrive['sdrive_account_shop_transaction_emails_subject']. ' - ' . strtoupper( $orderRoute ) );
		$mailer->SetMessage( $this->getFormattedOrderDetail( $orderRoute ) );

		// double check message and set it to something if the previous failed
		if( empty( $mailer->message ) ) {
			$mailer->SetMessage( _T('A purchase has been made in your store.') );
		}

		$mailer->Send();
	}


	/************************* only private methods below this line *********************/

	function _getAllProducts ( ) {
		
		$result = $this->db->query( 'SELECT * FROM ' . TPRODUCT , array( PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY ) );

		foreach( $result as $row ) {

			if( ord( $row['object'] ) == 123 )					// 123 is ascii value for {
				$this->products[ $row['productid'] ] = json_decode( $row['object'], true );
			else
				$this->products[ $row['productid'] ] = unserialize( $row['object'] );
		}
		return $this->products;
	}


	private function _getStarredProducts ( ) {

		if( $this->starredproducts !== false ) return;

		// no need to prepare, this query is shot once only
		$sql = "SELECT productid, groupid" .
			   " FROM " . TPRODUCT . " WHERE starred = 1";

		$this->starredproducts = $this->db->fetchAll( $sql );
	}


	private function _getCategoryProducts ( ) {

		if( $this->categoryproducts !== false )	return;

		// no need to prepare, this query is shot once only
		$sql = "SELECT productid, groupid" .
			   " FROM " . TPRODUCT . " WHERE catprod > 0";

		$this->categoryproducts  = $this->db->fetchAll( $sql );
	}


	private function _getGroups ( ) {

		if( $this->groups !== false ) return;

		$this->groups = array();

		if( ! $this->tableExists( TGROUP ) ) return;

		// use self join to get the parent name and pagehref
		$sql = "SELECT grp1.id AS groupid," .
			   " grp1.parentid, " .
			 //" grp1.type, " .				// always 'json'
			   " grp1.object " .
			   " FROM " . TGROUP . " grp1" .
			   " LEFT JOIN " . TGROUP . " grp2 ON grp1.id = grp2.parentid" .
			   ';';

		foreach( $this->db->query( $sql, PDO::FETCH_ASSOC ) as $row ) {

			$this->groups[ $row['groupid'] ] = json_decode( $row['object'], true );
			
			// the scripts use 'parentid', but the object has 'parentId'
			$this->groups[ $row['groupid'] ]['parentid'] = $row['parentid'];
		}
	}

	private function _getPages ( ) {

		if( $this->pages !== false ) return;

		// no need to prepare, this query is shot once only
		$sql = "SELECT * FROM " . TPAGE . ";";

		$this->pages = array();
		foreach( $this->db->query( $sql, PDO::FETCH_ASSOC ) as $row ) {

			$this->pages[ $row['id'] ] = $row;
		}
	}

	private function _getExtraShipping ( ) {

		if( $this->extrashipping !== false ) return;

		$sql = "SELECT * FROM " . TESHIP . ";";

		$data = $this->db->query( $sql, PDO::FETCH_ASSOC );
		$this->extrashipping = $data->fetchAll();

	}

	/*
	 * Sold items data structure: array[productid][type] = value
	 * The 'type field can have 2 values:
	 * 		'client'	<- what the SCC client has received
	 * 		'cart' 		<- where sales are added
	 *
	 *	Location of data:
	 *		With FBase					   With SQLite Database
	 *		$this->availableStock			 => part of the products in readDB
	 *		$this->getConfigS('sync_marker') => TCONFIG table in readDB, key FSCCSYNC, column 'shortval'
	 *		our copy of the sync_marker		 => TCONFIG table in writeDB, key FCARTSYNC, column 'shortval'
	 *		$this->soldItemsData			 => TSTOCK table, columns 'cart' and 'client'
	 */
	function _loadSoldItems ( ) {

		$writeDB = new Database( 'writeDB' );
		$result = $writeDB->query( 'SELECT * FROM ' . TSTOCK );

		if( $result === false )			return;

		foreach( $result as $row ) {
			$this->soldItemsData[ $row['productid'] ] [ 'cart' ] = $row['cart'];
			$this->soldItemsData[ $row['productid'] ] [ 'client' ] = $row['client'];
		}

		$sth = $writeDB->query( 'SELECT shortval FROM ' . TCONFIG . ' WHERE id=\'' . FCARTSYNC . '\';' );  
		if( $sth !== false )		$this->soldsyncmarker = $sth->fetchColumn();

	}


	function _saveSoldItems ( ) {

		$writeDB = new Database( 'writeDB' );
		$sth = $writeDB->prepare( 'INSERT OR REPLACE INTO ' . TSTOCK . '(productid, cart, client) VALUES (:pid, :cart, :client);' );

		foreach( $this->soldItemsData as $pid => $row ) {
			$sth->bindParam(':pid', $pid, PDO::PARAM_STR);
			$sth->bindParam(':cart', $row['cart'], PDO::PARAM_INT);
			$sth->bindParam(':client', $row['client'], PDO::PARAM_INT);
			$sth->execute( );
		}

	}

}


?>
