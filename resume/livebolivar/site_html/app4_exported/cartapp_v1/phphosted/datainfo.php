<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Inspector for shop data and versions of PHP scripts.
* Special Hosted/SDrive version that inspects the database
*
* Use servertest.php for all tests that do not depend on SCC.
* 
* Dump all shop data in a readable format for the support guys.
*
* @version $Revision: 2851 $
* @author Cees de Gruijter
* @category SCC S-Drive
* @copyright Copyright (c) 2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

define( 'MIN_BUILD', 1866);

if( ! file_exists('../store/shop.dat') ) {
	echo "<html><body><h1>Cannot find data file. Script must exit.</h1></body></html>";
	exit();
}

chdir( '../../' );

set_include_path( $absPath . 'ccdata' . PATH_SEPARATOR .
				  $absPath . 'ccdata/phphosted' . PATH_SEPARATOR .
				  $absPath . 'ccdata/phppro' . PATH_SEPARATOR .
				  $absPath . 'ccdata/php' . PATH_SEPARATOR .
				  $absPath . 'ccdata/datahosted' . PATH_SEPARATOR .
				  $absPath . 'ccdata/datapro' . PATH_SEPARATOR .
				  $absPath . 'ccdata/data' . PATH_SEPARATOR .
				  get_include_path() );

require 'config.inc.php';

$sccversion = '';
$message = '';
/*
if( isset( $config['sccversion']) ) {
	$sccversion = $config['sccversion'];
} else {
	echo "<html><body><h1>No version information is found in the data file. The data file must be outdated or damaged. Script must exit.</h1></body></html>";
	exit();
}

if( preg_match( '/\d{4,6}/', $sccversion, $matches ) == 1 &&
	(int) $matches[0] < MIN_BUILD )
{
	$message = "The PHP scripts expect <b><u>data</u></b> from a Shopping Cart Creator client that has build number " . MIN_BUILD . " or higher. ";
}

// test the versions of all PHP scripts we can find too
if( ! TestScriptVersions( MIN_BUILD, $message ) ) {
	$message .= "<br>Some PHP scripts did not pass the version test. Do a 'Full Upload' from the Shopping Cart Creator client to create a consistent set of scripts.";
}
*/

if( isset( $_POST['code'] ) && $_POST['code'] == md5( 'coffeeshop' . trim( $myPage->getConfigS('shopname') ) ) )
	$auth = true;
else
	$auth = false;


//---------------------- only function definition below this line ------------------//

function hashTable ( $titles, $hash ) {

	echo '<table>';
	tr ( $titles, 'head' );

	foreach( $hash as $name => $value ) {

		// filter some sensitive info
		$lkey = strtolower( $name );
		if( false !== strpos( $lkey, 'key') ||
		 	false !== strpos( $lkey, 'password' ) ||
		 	false !== strpos( $lkey, 'signature') ||
		 	false !== strpos( $lkey, 'sdrive_account_shared_key') ) {
			$value = str_repeat('*', strlen( $value) ) . '(' . strlen( $value) . ')';
		}

		// make bool readable
		if( is_bool( $value ) ) $value = $value ? 'yes' : 'no';

		// value could be a hash or an array
		if( is_array( $value ) )
			tr ( array( $name, implode( ' ; ', $value) ) );
		else
			tr ( array( $name, $value ) );
	}
	echo '</table>';
}


function col ( $widths ) {
	foreach( $widths as $width ) {
		echo '<col width="' . $width . '" />';
	}
}


function tr ( $cells, $class = '' ) {

	if ( !is_array( $cells) || empty( $cells ) ) return;

	if( empty( $class ) )
		echo '<tr>';
	else
		echo '<tr class="' . $class . '">';

	$repeat = count( $cells );
	for( $i = 0; $i < $repeat ; $i++ ) {
		td( $cells[$i], $class );
	}
	echo '</tr>';
}


function td ( $data, $class = '', $width = 0 ) {

	$attribs = ( $class == '' ? '' : ('class="' . $class . '"' ) )
			 . ' '
			 . ( $width ? ('width="' . $width . '"') : '' );
	$attribs = trim( $attribs);

	if( empty( $attribs ) )
		echo '<td>';
	else
		echo '<td ' . $attribs . '>';

	if( is_array( $data) ) {

		if( ! empty($data) ) {

			// test the first element for array type
			foreach( $data as $d1 ) {

				if( is_array( $d1 ) && !empty( $d1 ) ) {

					$keys = array_keys( $d1 );

					foreach( $keys as $key ) {

						if( is_array( $d1[ $key ] ) ) {
							// d1 can be an array of hash arrays
							foreach( $d1[ $key ] as $item ) {
								echo $item['label'] . ' | ' . $item['value'] . ' | ' . $item['selected'] . '<br>';
							}
							echo '<br>';
						} else {
							// d1 can be a normal array
							echo '<b>' . $d1[ $key ] . '</b><br>';
						}

					}

				} else {

					echo implode( '; ', $data );
					break;

				}
			}
		}
	}
	else {
		echo $data;
	}
	echo '</td>';
}


// return false if any of the encountered scripts has version lower than specified
// If a PHP file contains no version string, ignore it.
// Else the version bust be => the required version.
function TestScriptVersions ( $min_build, &$message ) {

	// folders that may contain files for version testing, relative to where this script is
	$dirs = array( '.', '../data', '../..' );

	foreach( $dirs as $dir ) {

		$rpath = realpath( $dir );
		if( ! $rpath || ! is_dir( $rpath ) ) {
			$message .= ' Path "' . $dir . '" does not exist or is not a directory.';
			return false;
		}

		$handle = opendir( $rpath );
		if( $handle ) {

			while( false !== ( $file = readdir($handle) ) ) {

				// only deal with php files
				if( substr( $file, strlen( $file ) - 4 ) != '.php' ) {
					continue;
				}

				$fh = fopen( $rpath . '/' . $file, 'r' );
				if( ! $fh ) {
					$message .= ' Cannot open file "' . $file . '".';
					return false;
				}

				$fversion = ScanVersion( $fh );
				fclose( $fh );

				if( $fversion != -1 && $fversion < $min_build ) {
					$message = ' File "' . $file . '" has version ' . $fversion . ' while a minimum build number of ' . $min_build . ' is needed.';
					return false;
				}

	    	}
	    	closedir( $handle );

		} else {

			$message = "Directory '" . $rpath . "' could not be opened.";
			return false;

		}
	}
	return true;
}


// read the first 50 lines and return -1 if not found or the version number
function ScanVersion ( $handle ) {

	$txt = '';

	for( $i = 0; $i < 50 && $txt !== false ; ++$i ) {
		$txt = fgets( $handle );
		// @version $Revision: 2851 $
		if( preg_match( '/@version \$Revision:\s+(\d+)\s+\$/', $txt, $matches ) ) {
			return $matches[1];
		}

	}
	return -1;
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<style>
.head {
	color: #ffffff;
	background-color: #999;
}
td {
	vertical-align: top;
	padding-right: 5px;
	padding-left: 5px;
	border-right: 1px solid lightgrey;
	border-bottom: 1px solid lightgrey;
}
h2 {
	margin-top: 30px;
}
span.small {
	margin-left: 5px;
	font-size: small;
}
</style>
</head>
<body>
<?php if( ! empty($sccversion) ) { ?>
	<p style="font-style:italic;">CoffeeCup ShoppingCart Creator - Client <?php echo $sccversion; ?></p>
<?php } ?>

<?php if( ! empty($message) ) { ?>
	<p style="color: navy;background-color: #ffeeb0; border: 1px solid #f00;width:70%; padding:15px;"><?php echo $message; ?></p>
<?php } ?>

<form method="POST">
Key: <input type="text" name="code">
<input type="submit">
</form>
<hr>
<?php if( $auth ) { ?>
<h2>Configuration</h2>
<table>
<?php
	// get the config array
	$config =& $myPage->db->fetchAll( 'SELECT * FROM ' . TCONFIG . ' WHERE type = \'\' OR type = \'longval\';');
	#var_dump($config);

	foreach( $config as $key => $value ) {
		#var_dump($key);
		#var_dump($value);
		if( $value['type'] == '' )
			tr( array( $value['id'], $value['shortval'] ) );
		else
			tr( array( $value['id'], htmlentities($value['longval']) ) );
	}
?>
</table>
<?php
	$config =& $myPage->db->fetchAll( 'SELECT * FROM ' . TCONFIG . ' WHERE type = \'json\' AND id NOT LIKE \'TaxRates%\' AND NOT id = \'ShippingRates\';');
	#var_dump($config);
	foreach( $config  as $key => $value ) {
		if( is_array( $value ) )
		{
			echo '<h2>Configuration - ' . $value['id'] . '</h2>';
			hashTable( array( 'Key', 'Value'), json_decode( $value['longval'], true ) );
		}
	}
?>
<h2> S-Drive Configuration</h2>
<?php
	if( $myPage->sdrive == false ) {
		echo "No configuration found";
	} else {
		hashTable( array( 'key' => 'value'), $myPage->sdrive ); 
	}
?>

<h2>Pages</h2>
<table>
<?php
	$pages =& $myPage->db->fetchAll( 'SELECT * FROM ' . TPAGE . ';');
	#var_dump( $pages );
	
	tr ( array( 'id', 'Name', 'Type', 'Url', 'Meta Descr.', 'Keywords', 'Content'), 'head' );
	foreach( $pages as $page ) {
		tr( array( $page['id'], $page['name'], $page['type'], $page['pagehref'], $page['metadescr'], $page['metakeyw'], htmlentities($page['content']) ) );
	}
?>
</table>


<h2>Tax Rates Products</h2>
<table>
<?php
	// the rates array has 2 keys (location and rate_name), we must order the array so
	// that the locations can be used as rows and the rate_names as column headings
	$data =& $myPage->db->fetchAll( 'SELECT * FROM ' . TCONFIG . ' WHERE type = \'json\' AND id =\'TaxRates:Product\';');
	#var_dump( $data );
	$prodrates = json_decode( $data[0]['longval'], true );
	#var_dump( $prodrates);
	
	if( is_array( $prodrates ) && count($prodrates) > 0 ) {
		// do products first
		$rownames = array_keys( $prodrates );
		$colnames = array_keys( $prodrates[ $rownames[0] ] );

		array_unshift( $colnames, '&nbsp;' );
		tr ( $colnames, 'head' );
		array_shift( $colnames);

		$taxlocations = $myPage->getConfigS('TaxLocations');

		foreach( $rownames as $rn ) {
			#var_dump($rn);
			$r = array( $taxlocations[$rn] );
			#var_dump($r);
			foreach( $colnames as $cn ) {
				$r[] = $prodrates[$rn][$cn] / pow(10, $myPage->getConfigS('TaxRatesDecimals') );
			}
			tr( $r );
		}
	} else {
		echo 'None defined.';
	}
?>
</table>

<h2>Shipping Rates based on Weights</h2>
<table>
<?php
	// the rates array has 2 keys (location and methods), we must order the array so
	// that the locations can be used as rows and the rate_names as column headings

	$data =& $myPage->db->fetchAll( 'SELECT * FROM ' . TCONFIG . ' WHERE id = \'ShippingRates\';');
	$rates = json_decode( $data[0]['longval'], true);
	#var_dump( $rates );
	$taxlocs =& $myPage->getConfigS('TaxLocations');
	
	if( is_array( $rates ) && count($rates) > 0 ) {
		$rownames = array_keys( $rates );
		$colnames = array_keys( $rates[ $rownames[0] ] );

		array_unshift( $colnames, '&nbsp;' );
		tr ( $colnames, 'head' );
		array_shift( $colnames);

		foreach( $rownames as $rn ) {

			$r = array( $taxlocs[$rn] );

			foreach( $colnames as $cn ) {

				$tmp = '';

				foreach( $rates[$rn][$cn] as $weight => $value ) {

					if( ! empty( $tmp ) ) $tmp .= ' | ';

					switch ( $weight ) {
						case -1:
							$tmp .= 'over';
							break;
						case -2:
							$tmp .= 'not available';
							break;
						default:
							$tmp .= $weight / pow(10, $myPage->getConfigS('ShippingRangeDecimals') );
					}
					if( $weight != -2 ) {
						$tmp .= ' - ' . $value / pow(10, $myPage->getConfigS('ShippingRatesDecimals') );
					}
				}
				$r[] = $tmp;
			}
			tr( $r );
		}
	} else {
		echo 'None defined.';
	}
?>
</table>



<h2>Locations for Tax and Shipping Rates</h2>
<table>
<?php
	// furthermore, there are separate rates for products and shipping
	$data =& $myPage->db->fetchAll( 'SELECT * FROM ' . TCONFIG . ' WHERE id=\'TaxRates:Shipping\';');
	$shiprates = json_decode( $data[0]['longval'], true );
	#var_dump( $shiprates );
	$taxlocations = $myPage->getConfigS('TaxLocations');
	
	tr ( array('ID', 'Location', 'Rate'), 'head' );

	foreach( $shiprates as $key => $amount ) {
		$r = array( $key );
		$r[] = $taxlocations[$key];
		$r[] = $amount / pow(10, $config['TaxRatesDecimals']);
		tr( $r );
	}
?>
</table>

<h2>Extra Shipping Options</h2>
<table>
<?php
	$extrashippings = $myPage->db->fetchAll('SELECT * FROM ' . TESHIP .';');
	#var_dump( $extrashippings  );
	tr ( array( 'Descr', 'Amount', 'Type', 'Visible', 'ID'), 'head' );
	foreach( $extrashippings as $row ) {
		tr ( array( $row['descr'], $row['amount'] / pow(10, $myPage->getConfigS('ShippingRatesDecimals') ), $row['type'], $row['visible'], $row['id'] ) );
	}
?>
</table>
<h2>Starred Products</h2>
<table>
<?php
	$starredproducts =& $myPage->db->fetchAll('SELECT productid, groupid FROM ' . TPRODUCT.' WHERE starred = 1;');
	#var_dump( $starredproducts );
	tr ( array( 'Product ID', 'Group ID'), 'head' );
	foreach( $starredproducts as $row ) {
		tr ( array_values( $row ) );
	}
?>
</table>
<h2>Groups</h2>
<table>
<?php
	$groups =& $myPage->db->fetchAll('SELECT * FROM ' . TGROUP. ';');
	#var_dump( $groups );

	tr ( array( 'ID', 'Name', 'Meta-Keywords', 'Meta-Description', 'URL', 'Parent ID', 'Image' ), 'head' );
	foreach( $groups as $row ) {
		tr ( array( $row['id'], $row['name'], $row['metakeyw'], $row['metadescr'], $row['pagehref'], $row['parentid'], $row['image'])  );
	}
?>
</table>
<h2>Products</h2>
<?php
	$products =& $myPage->db->fetchAll('SELECT * FROM ' . TPRODUCT. ';');

	$colwidths = array ( 50, 50, 150, 200, 200, 600, 200, 300, 50, 50, 50,
						 20, 20, 50, 50, 50, 20, 20, 20, 50, 50,
						150, 20, 30, 50, 20, 20, 200, 200, 300,
						 50, 20 );
	echo '<table width="' . array_sum( $colwidths ) . '">';
	col( $colwidths );
	tr ( array( 'ID', 'Group', 'Url', 'Name', 'Short', 'Long', 'Meta-Keywords', 'Meta-Description', 'Weight', 'Units', 'Digits',
				'Starred', 'Your $', 'Retail $', 'Discount', 'Is %', 'Tax', 'Stock', 'Show', 'Shipping', 'Handling',
				'Option', 'Enforce', 'Qty Type', 'Qty Default', 'Qty Min', 'Qty Max', 'Img Full', 'Img Small', 'Thumbs',
				'SKU', 'Show Weight'), 'head');
	foreach( $products as $row ) {
		$product = json_decode( $row['object'], true );
		#var_dump( $product );
		tr ( array( $product['productid'], $product['groupid'], $product['pagehref'], $product['name'], $product['shortdescription'], $product['longdescription'], $product['metakeywords'], $product['metadescription'], $product['weight'], $product['weightunits'], $product['weightdigits'],
		  			$product['isstarred'], $product['yourprice'], $product['retailprice'], $product['discount'], $product['ispercentage'], $product['tax'], $product['stock'], $product['showstock'], $product['shipping'], $product['handling'],
					$product['options'], $product['forceoptions'], $product['typequantity'], $product['defaultquantity'], $product['minrangequantity'], $product['maxrangequantity'], $product['main_full'], $product['main_small'], $product['thumbs'],
					$product['sku'], $product['showweight']) );
	}
	?>
</table>
<?php } ?>
</body>
</html>