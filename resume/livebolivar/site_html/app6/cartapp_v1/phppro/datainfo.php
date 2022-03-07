<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Inspector for shop data and versions of PHP scripts.
* Use servertest.php for all tests that do not depend on SCC.
*
* Dump all shop data in a readable format for the support guys.
*
* @version $Revision: 2851 $
* @author Cees de Gruijter
* @category SCC
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (https://www.coffeecup.com/)
*/

define( 'MIN_BUILD', 1866);


if( ! file_exists('../data/data.php') ) {
	echo "<html><body><h1>Cannot find data file. Script must exit.</h1></body></html>";
	exit();
}

include '../data/data.php';

$sccversion = '';
$message = '';

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

if( isset( $_POST['code'] ) && $_POST['code'] == md5( 'coffeeshop' . trim( $config['shopname']) ) )
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
		 	false !== strpos ( $lkey, 'signature') ) {
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
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
	foreach( $config as $key => $value ) {
		if( ! is_array( $value ) )
			tr( array( $key, $value ) );
	}
?>
</table>
<?php
	foreach( $config  as $key => $value ) {
		if( is_array( $value ) &&
			!( $key == 'TaxRates' || $key == 'TaxLocations' || $key == 'ShippingRates' ) )
		{
			echo '<h2>Configuration - ' . $key . '</h2>';
			hashTable( array( 'Key', 'Value'), $value );
		}
	}
?>

<h2>Pages</h2>
<table>
<?php
	tr ( array( 'id', 'Name', 'Type', 'Url', 'Meta Descr.', 'Keywords', 'Content'), 'head' );
	foreach( $pages as $page ) {
		tr( array( $page['id'], $page['name'], $page['type'], $page['pagehref'], $page['metadescription'], $page['metakeywords'], $page['content'] ) );
	}
?>
</table>


<h2>Tax Rates Products</h2>
<table>
<?php
	// the rates array has 2 keys (location and rate_name), we must order the array so
	// that the locations can be used as rows and the rate_names as column headings
	$prodrates = $config['TaxRates']['Product'];

	if( is_array( $prodrates ) ) {
		// do products first
		$rownames = array_keys( $prodrates );
		$colnames = array_keys( $prodrates[ $rownames[0] ] );

		array_unshift( $colnames, '&nbsp;' );
		tr ( $colnames, 'head' );
		array_shift( $colnames);

		foreach( $rownames as $rn ) {
			$r = array( $config['TaxLocations'][$rn] );
			foreach( $colnames as $cn ) {
				$r[] = $prodrates[$rn][$cn] / pow(10, $config['TaxRatesDecimals']);
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

	if( is_array( $config['ShippingRates'] ) ) {
		$rownames = array_keys( $config['ShippingRates'] );
		$colnames = array_keys( $config['ShippingRates'][ $rownames[0] ] );

		array_unshift( $colnames, '&nbsp;' );
		tr ( $colnames, 'head' );
		array_shift( $colnames);

		foreach( $rownames as $rn ) {

			$r = array( $config['TaxLocations'][$rn] );

			foreach( $colnames as $cn ) {

				$tmp = '';

				foreach( $config['ShippingRates'][$rn][$cn] as $weight => $value ) {

					if( ! empty( $tmp ) ) $tmp .= ' | ';

					switch ( $weight ) {
						case -1:
							$tmp .= 'over';
							break;
						case -2:
							$tmp .= 'not available';
							break;
						default:
							$tmp .= $weight;
					}
					if( $weight != -2 ) {
						$tmp .= ' - ' . $value / pow(10, $config['ShippingRatesDecimals']);
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
	$shiprates = $config['TaxRates']['Shipping'];

	tr ( array('ID', 'Location', 'Rate'), 'head' );

	foreach( $shiprates as $key => $amount ) {
		$r = array( $key );
		$r[] = $config['TaxLocations'][$key];
		$r[] = $amount / pow(10, $config['TaxRatesDecimals']);
		tr( $r );
	}
?>
</table>

<h2>Extra Shipping Options</h2>
<table>
<?php
	tr ( array( 'Descr', 'Amount', 'Type', 'Visible', 'ID'), 'head' );
	foreach( $extrashippings as $row ) {
		tr ( array_values( $row ) );
	}
?>
</table>
<h2>Starred Products</h2>
<table>
<?php
	tr ( array( 'Product ID', 'Group ID'), 'head' );
	foreach( $starredproducts as $row ) {
		tr ( array_values( $row ) );
	}
?>
</table>
<h2>Groups</h2>
<table>
<?php
	tr ( array( 'Name', 'Meta-Keywords', 'Meta-Description', 'ID', 'URL', 'Contains Products', 'Parent ID', 'Parent Name', 'Parent HREF', 'Image',  'Contains Sub Cats.' ), 'head' );
	foreach( $groups as $row ) {
		tr ( array_values( $row)  );
	}
?>
</table>
<h2>Products</h2>
<?php
	$colwidths = array ( 50, 50, 150, 200, 200, 600, 200, 300, 50, 50,
						 20, 20, 50, 50, 50, 20, 20, 20, 50, 50,
						150, 50, 50, 20, 20, 200, 200, 300 );
	echo '<table width="' . array_sum( $colwidths ) . '">';
	col( $colwidths );
	tr ( array( 'ID', 'Group', 'Url', 'Name', 'Short', 'Long', 'Meta-Keywords', 'Meta-Description', 'Weight', 'Units',
				'Starred', 'Ref.', 'Your $', 'Retail $', 'Discount', 'Is %', 'Tax', 'Stock', 'Shipping', 'Handling',
				'Option', 'Qty Type', 'Qty Default', 'Qty Min', 'Qty Max', 'Img Full', 'Img Small', 'Thumbs'), 'head');
	foreach( $products as $product ) {
		tr ( array_values( $product ) );
	}
	?>
</table>
<?php } ?>
</body>
</html>