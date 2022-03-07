<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Pickup the data.inc file and dump it in a database
*
* @author Cees de Gruijter
* @category SCC Hosted - S-Drive
* @copyright Copyright (c) 2010 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/


define( 'NOPAGE', 'NOPAGE' ); // signal that we don't need the page instance

// fix the include path because we're not in the shop root
chdir( '../../' );
require 'config.inc.php';
require 'data.inc';
require 'data_stock.inc';
require 'databaseloader.cls.php';

// page dies if the authentication fails
//$users = $config['users'];
//Authenticate( $users, 'Data Loader' );

$d = json_decode( $data, true );
//var_dump( $d );

$db = new DatabaseLoader();

// prepare the table
echo '<html><pre>';
echo "(re-)create CONFIG table\n";

try {

	// create table with default options
	$db->createConfigTable();

	$count = 0; $i = 0;
	foreach( $d['config'] as $key => $value ) {

		if( is_array( $value ) ) {

			// some config data does not have a secondary key
			switch( $key ) {

			case 'ShippingRates':
			case 'TaxLocations':
			case 'users';

					$rs = $db->insertConfigRow(  $key, $value );
					if( $rs !== false ) $count += $rs;

					$i++;
				break;

			default:

				foreach( $value as $subkey => $subval ) {

					$rs = $db->insertConfigRow(  $key . ':' . $subkey, $subval );
					if( $rs !== false ) $count += $rs;

					$i++;
				}
			}

		} else  {

			$rs = $db->insertConfigRow(  $key, $value );
			if( $rs !== false ) $count += $rs;

			$i++;

		}

	}
	echo $count . ' of ' . $i . " config items inserted.\n\n";

	echo "(re-)create PRODUCTS table\n";

	$db->createProductTable();

	$count = 0; $i = 0;
	foreach( $d['products'] as $key => $value ) {

		$rs = $db->insertProductRow(  (int)$key, $value );
		if( $rs !== false ) $count += $rs;

		$i++;

	}
	echo $count . ' of ' . $i . " products inserted.\n";

	// Use the $categoryproducts array to add flags to the products,
	// number the flags so we can recover the exact order of the array too
	$count = 0; $j = 1;
	foreach( $d['categoryproducts'] as $cp ) {
		$count += $db->updateProductRow(  (int)$cp['productid'], array( 'catprod' => $j++ ) );
	}
	echo $count . ' of ' . $i . " products updated.\n\n";

	echo "(re-)create SEARCHPRODUCTS table\n";

	$db->createProductSearchTable();

	$count = 0; $i = 0;
	foreach( $d['products'] as $key => $value ) {

		$rs = $db->insertProductSearchRow(  (int)$key, $value );
		if( $rs !== false ) $count += $rs;

		$i++;

	}
	echo $count . ' of ' . $i . " searchable products inserted.\n";
//// not a default sqlite module
////	$db->alterProductSearchTable();
////	echo "Adding full text search indexes\n\n";


	echo "(re-)create GROUPS table\n";

	$db->createGroupTable();

	$count = 0; $i = 0;
	foreach( $d['groups'] as $key => $value ) {

		$rs = $db->insertGroupRow(  (int)$key, $value );
		if( $rs !== false ) $count += $rs;

		$i++;

	}

	echo $count . ' of ' . $i . " groups inserted.\n\n";


	echo "(re-)create PAGES table\n";
	$db->createPagesTable();

	$count = 0; $i = 0;
	foreach( $d['pages'] as $key => $value ) {

		$rs = $db->insertPagesRow(  (int)$key, $value );
		if( $rs !== false ) $count += $rs;

		$i++;

	}
	echo $count . ' of ' . $i . " pages inserted.\n\n";

	echo "(re-)create SHIPPING table\n";
	$db->createTableExtraShipping();

	$count = 0; $i = 0;
	foreach( $d['extrashippings'] as $key => $value ) {

		$rs = $db->insertExtraShippingRow(  (int)$key, $value );
		if( $rs !== false ) $count += $rs;

		$i++;

	}
	echo $count . ' of ' . $i . " shipping definitions inserted.\n\n";


	echo "Creating clean Stock Log table\n";
	$db->createStocksTabel();

	// load the stock list
	$sm = json_decode( $sync_marker_json, true );
	$ps = json_decode( $products_stock_json, true );

	$count = 0; $i = 0;
	foreach( $ps['products'] as $key => $prd ) {

		$rs = $db->insertStocksTableRow(  (int)$key, $prd['stock'] );
		if( $rs !== false ) $count += $rs;

		$i++;
	}

	// lastly, insert the sync markers in the config table
	$db->insertConfigRow( FSCCSYNC, $sm['sync_marker'] );
	$db->insertConfigRow( FCARTSYNC, $sm['sync_marker'] );

	echo $count . ' of ' . $i . " stock items inserted.\n\n";


	if( $db->writeDB->tableExists( TTRANS ) ){
		echo "TRANSACTION table exists with these fields:\n";
		echo implode( ', ', $db->writeDB->getTableFields( TTRANS )) ."\n\n";
	} else {
		echo "Creating TRANSACTION table\n\n";
		$db->createTableTransactions();
	}


	if( isset( $d['config']['digital_downloads'] ) && $d['config']['digital_downloads'] == true ) {

		if( $db->writeDB->tableExists( TDOWNL ) ){
			echo "Digital DOWNLOAD table exists with these fields:\n";
			echo implode( ', ', $db->writeDB->getTableFields( TDOWNL )) ."\n";
		} else {
			echo "(re-)create DIGITAL DOWNLOAD table\n\n";
			$db->createTableDownloads();
		}

	}

} catch ( Exception $e ) {

	echo 'Line ' . $e->getLine() . ' in ' . $e->getFile() . "\n";
	echo $e->getMessage();
	exit(1);
}

echo '</pre></html>';
?>
