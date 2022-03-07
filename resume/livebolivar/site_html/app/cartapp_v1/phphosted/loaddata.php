<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Pickup the data.php file and dump it in a database
*
* @author Cees de Gruijter
* @category SCC Hosted
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (https://www.coffeecup.com/)
*/


define( 'NOPAGE', 'NOPAGE' ); // signal that we don't need the page instance

require './ccdata/php/utilities.inc.php';

require 'data/data.php';
require 'databaseloader.cls.php';

// page dies if the authentication fails
$users = $config['users'];
Authenticate( $users, 'Data Loader' );

$db = new DatabaseLoader();

// prepare the table
echo '<html><pre>';
echo "(re-)create CONFIG table\n";

// put this in a transaction
$db->beginTransaction();
try {

	// create table with default options
	$db->createConfigTable();


	 $count = 0; $i = 0;
	foreach( $config as $key => $value ) {

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
	foreach( $products as $key => $value ) {

		$rs = $db->insertProductRow(  (int)$key, $value );
		if( $rs !== false ) $count += $rs;

		$i++;

	}
	echo $count . ' of ' . $i . " products inserted.\n";


	/* Use the $categoryproducts array to add flags to the products,
	 * number the flags so we can recover the exact order of the array too
	 */
	$count = 0; $j = 1;
	foreach( $categoryproducts as $cp ) {
		$count += $db->updateProductRow(  (int)$cp['productid'], array( 'catprod' => $j++ ) );
	}
	echo $count . ' of ' . $i . " products updated.\n\n";

	echo "(re-)create SEARCHPRODUCTS table\n";

	$db->createProductSearchTable();

	$count = 0; $i = 0;
	foreach( $products as $key => $value ) {

		$rs = $db->insertProductSearchRow(  (int)$key, $value );
		if( $rs !== false ) $count += $rs;

		$i++;

	}
	echo $count . ' of ' . $i . " searchable products inserted.\n";
	$db->alterProductSearchTable();
	echo "Adding full text search indexes\n\n";



	echo "(re-)create GROUPS table\n";

	$db->createGroupTable();

	$count = 0; $i = 0;
	foreach( $groups as $key => $value ) {

		$rs = $db->insertGroupRow(  (int)$key, $value );
		if( $rs !== false ) $count += $rs;

		$i++;

	}

	echo $count . ' of ' . $i . " groups inserted.\n\n";


	echo "(re-)create PAGES table\n";
	$db->createPagesTable();

	$count = 0; $i = 0;
	foreach( $pages as $key => $value ) {

		$rs = $db->insertPagesRow(  (int)$key, $value );
		if( $rs !== false ) $count += $rs;

		$i++;

	}
	echo $count . ' of ' . $i . " pages inserted.\n\n";

	echo "(re-)create SHIPPING table\n";
	$db->createTableExtraShipping();

	$count = 0; $i = 0;
	foreach( $extrashippings as $key => $value ) {

		$rs = $db->insertExtraShippingRow(  (int)$key, $value );
		if( $rs !== false ) $count += $rs;

		$i++;

	}
	echo $count . ' of ' . $i . " shipping definitions inserted.\n\n";


	echo "Creating clean Stock Log table\n";
	$db->createStocksTabel();

	// load the stock list
	unset( $products );
	require 'data/data_stock.php';

	$count = 0; $i = 0;
	foreach( $products as $key => $prd ) {

		$rs = $db->insertStocksTableRow(  (int)$key, $prd['stock'] );
		if( $rs !== false ) $count += $rs;

		$i++;
	}

	// lastly, insert the sync markers in the config table
	$db->insertConfigRow( FSCCSYNC, $sync_marker );
	$db->insertConfigRow( FCARTSYNC, $sync_marker );

	echo $count . ' of ' . $i . " stock items inserted.\n\n";


	if( $db->tableExists( TTRANS ) ){
		echo "TRANSACTION table exists with these fields:\n";
		echo implode( ', ', $db->getTableFields( TTRANS )) ."\n\n";
	} else {
		echo "(re-)create TRANSACTION table\n\n";
		$db->createTableTransactions();
	}


	if( $config['digital_downloads'] == true ) {

		if( $db->tableExists( TDOWNL ) ){
			echo "Digital DOWNLOAD table exists with these fields:\n";
			echo implode( ', ', $db->getTableFields( TDOWNL )) ."\n";
		} else {
			echo "(re-)create DIGITAL DOWNLOAD table\n\n";
			$db->createTableDownloads();
		}

	}


} catch ( Exception $e ) {

	// don't count on this to work, because we asked for myIsam engine
	$db->rollBack();

	echo 'Line ' . $e->getLine() . ' in ' . $e->getFile() . "\n";
	echo $e->getMessage();
	exit(1);
}

// if we arrived here, everything must have worked.
$db->commit();

echo '</pre></html>';
?>
