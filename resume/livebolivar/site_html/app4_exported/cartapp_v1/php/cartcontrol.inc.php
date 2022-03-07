<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Use as include file
* Adds static functions for adding and removing products to the cart.
*
* @version $Revision: 2864 $
* @author Cees de Gruijter
* @category SCC PRO
* @copyright Copyright (c) 2009-2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

if( $myPage->sdrive_stats ) {	
	include_once 'statsreporter.php';
}

// A cartid other then '' means an item already in the cart should be updated.
// return the cartid of the modified item.
function cart_add ( &$cartid ) {

	global $myPage;

	$msg = false;
	// some input validation
	$cartid = ''; $productid = ''; $quantity = '';
	$num = extract( $_POST, EXTR_IF_EXISTS );

	if( ! is_numeric( $productid ) || $num < 1) {
		$msg = _T("Missing Product information in request to the Shopping Cart.");
		return $msg;
	}

	$product =& $myPage->getProduct( $productid, false );

	if( $product === false ) {
		return _T('Could not find product data.');
	}

	// get the option values, $optionstring must be a comma seperated list of values
	$optionstring = '';

	for ( $i = 0; $i < count($product['options']); ++$i ) {		// loop as many times as there are option fields

		$key = 'opt_' . $i;

		if( isset($_POST[$key]) ) {
			
			// If the key is -1 the user needs to select a required option
			if( $_POST[$key] == -1 && $product['forceoptions'] == '1' ) {
				
				$msg = $msg . _T("Please choose an option for ") . strtoupper($product['options'][$i]['name']) . '<br />' ;
			
			} else {
				
				// Sets the option as selected for that item
				$product['options'][$i]['items'][$_POST[$key] - 1]['selected'] = 1;
				$optionstring .= $_POST[$key] . ',';
			}
		} else {
			$optionstring .= '-1,';				// default
		}
	}
	
	if( $msg ) {
		return $msg;
	}
	$optionstring = rtrim( $optionstring, ',' );

	// after this test, we may assume $quantity is a valid number
	if( ($msg = cart_testQuantity( $quantity, $product, true, $optionstring) ) != '' ) {
		return $msg;
	}

	$currentqty = 0;
	
	if( $cartid != '' ) {
		
		// update existing cart entry
		$myPage->cart->setUnitsOfProduct( $cartid, $quantity );
		$myPage->cart->setOptionsOfProduct( $cartid, $optionstring );

	} else {

		// get unformatted product data (money in int format)
		$cartid = $myPage->cart->addProduct( $product, $quantity, $optionstring );

		// only report first time additions to the cart, ignore updates	
		if( $myPage->sdrive_stats ) {			
			$sr = new StatsReporter( $myPage );
			$sr->NotifyCartAdd( $product['sku'], $product['name'], $quantity );
		}
	}

	
	// after updating the cart, we must reset any pending authorisation with PayPal or
	// checkout won't work

	return $msg;
}


// Update quantities and return array of updated cartid's
function cart_update ( &$cartids ) {

	global $myPage;

	$msg = false;

	// input validation
	if( ! isset( $_POST['extrashipping'] ) &&  ! isset( $_POST['taxlocation'] )  &&
		( ! isset( $_POST['qty'] ) || ! is_array( $_POST['qty'] ) || empty( $_POST['qty'] ) ) ) {
		// nothing to do
		$msg = _T("Cart is up to date.");
		return $msg;
	}

	if( isset( $_POST['qty'] ) && is_array( $_POST['qty'] ) && ! empty( $_POST['qty'] ) ) {

		foreach ( $_POST['qty'] as $cid => $qty ) {

			// after this test, we may assume $quantity is a valid number
			$productid = $myPage->cart->getId( $cid );
			$product =& $myPage->getProduct( $productid, false );
			if( ($msg = cart_testQuantity($qty, $product, false)) != '' )
			{
				$msg = $myPage->cart->getName( $cid ) . ':<br/>' . $msg;
				break;
			}

			if ( is_numeric($qty) && $myPage->cart->setUnitsOfProduct($cid, $qty) ) {
				$cartids[] = $cid;
			} else {
				$msg = _T("Can not update a product that is not found in the cart or a quantity that is not a number.");
				break;
			}
		}

		if( count($cartids) == 0 && $msg == '')
			$msg = _T("No products in the cart were updated.");
	}

	if( isset($_POST['taxlocation']) ) {
		$myPage->cart->setTaxLocationId( $_POST['taxlocation'] );
	}

	// do this after the location, because location is used for weight based shipping costs
	if( isset( $_POST['extrashipping'] ) ) {

		if( ! $myPage->cart->setExtraShippingType( $_POST['extrashipping'] ) ) {
			$myPage->setCartMessage( _T("The total weight or volume of the products in your cart is too large for the shipping method that you selected.<br><br>Please choose another shipping method or contact our shop.") );
		}
	}

	return $msg;
}


// return array of cartids removed
function cart_remove (  &$cartids ) {

	global $myPage;

	$msg = false;
	$cartid = '';

	// input validation
	if( !is_array($_POST['delete']) || empty($_POST['delete']) ) {
		// nothing to do
		$msg = _T("No products in cart could be deleted.");
		return $msg;
	}

	// data structure is like this: [delete] => Array( [ 0 ] => Delete )
	// in which the array-key is the cartid we need (watch out for spaces!)
	$cids = array_keys($_POST['delete']);

	if( $myPage->sdrive_stats ) {			
		$sr = new StatsReporter( $myPage );
	}
	
	foreach( $cids as $cid ) {

		if( $myPage->sdrive_stats )			$prd = $myPage->cart->Prods[$cid];

		if( $myPage->cart->removeProduct( $cid ) ) {

			$cartids[] = $cid;

			if( $myPage->sdrive_stats ) {		
				$sr->NotifyCartRemoved( $prd['sku'], $prd['name'] );
			}
		}
	}

	if( count($cids) != count($cartids) ) {
		$msg = _T("Could not remove all items from cart.");
	}

	return $msg;
}


/*
 * Test if $quantity contains a valid value or return default if input $quantity =''
 *
 * Return value is '' on success or a descriptive error message on failure
 *
 *  There are 3 'typequantity' conditions:
 *		'choose_quantity' 	- any number will do
 * 		'default_quantity' 	- only the default is allowed
 * 		'range_quantity'	- number must be in range
 *
 *  Behavior in case $quantity = '' or -1:
 * 		'choose_quantity' 	- +1
 * 		'default_quantity' 	- +dft if not yet in cart
 * 		'range_quantity'	- +1   if still possible
 *
 * Quantities must be tested against what is already in the cart!
 * Added test against avalailable stock.
 */
function cart_testQuantity ( &$quantity, $product, $includeCart = true, $optionstring = '' ) {

	global $myPage;

	if( $includeCart )
		$inCart = $myPage->cart->getNumberOfOptionProducts( $product['productid'], $optionstring );
	else
		$inCart = 0;

	if( $myPage->getConfigS( 'track_stock' ) ) {

		$inStock = $myPage->getActualStockQty( $product['productid'] );

		// not enough in stock, we're done already
		if( $inStock <= 0 )
		{
			return sprintf( _T('"%s" is sold out.'), $product['name'] );
		}

	} else
		$inStock = false;


	// first deal with the situation when no quantity is defined
	if( $quantity == '' || $quantity == '-1' ) {

		switch ( $product['typequantity'] ) {

		case 'default_quantity':

			if( $inCart < $product['defaultquantity'] )
			{
				$quantity = (int)$product['defaultquantity'];
			}
			else
			{
				// allowed quantity is already in cart
				$quantity = -1;
			}

			break;

		case 'choose_quantity':
			$quantity = 1;		// 1 is a good number
			break;

		case 'range_quantity':
			if( $inCart < $product['maxrangequantity'] ) {
				if( $inCart > 0 )
				{
					$quantity = 1;
				}
				else
				{
					$quantity = (int)$product['minrangequantity'];
				}

			} else {
				// allowed quantity is already in cart
				$quantity = -1;
			}
		}

		if( $quantity < 0 ) return _T('This product is already in your shopping cart.');

		if( $inStock !== false && $quantity > $inStock ) {

			$quantity = $inStock;

			if( $inStock == 1 )
				return 	_T('There is only 1 unit left in stock for this product.');
			else
				return 	sprintf( _T('There are only %d units left in stock for this product.'), $inStock );
		}

		return '';
	}

	// now handle the situation with a qty from the user
	$quantity = (int) $quantity;
	$isNum = ( $quantity > 0 );

	if( $product['typequantity'] == 'range_quantity' &&
		( ! $isNum ||
		  $quantity < ($product['minrangequantity'] - $inCart) ||
		  $quantity > ($product['maxrangequantity'] - $inCart) ) )
	{
		$msg = sprintf( _T("Quantity must be a number between %d and %d."),
			 		$product['minrangequantity'],
			 		$product['maxrangequantity'] );
		if( $inCart )
			$msg .= sprintf( _T(" <br/>You have this product %d times in your cart."), $inCart );

		return $msg;
	}

	if( $product['typequantity'] == 'choose_quantity' && !$isNum )
	{
		$msg = _T("Quantity must be a whole number and may not be 0.");
		return $msg;
	}

	if( $product['typequantity'] == 'default_quantity' &&
		$quantity != $product['defaultquantity'] )
	{
		// only default quantity allowed
		$quantity = $product['defaultquantity'];
		$msg = sprintf( _T("Quantity can only be: %d."), $quantity );
		return $msg;
	}

	// sold out if the minimum qty is more than the available stock
	if( $inStock !== false && $quantity > $inStock ) {
		if( $inStock == 1 )
			return 	_T('There is only 1 unit left in stock for this product.');
		else
			return 	sprintf( _T('There are only %d units left in stock for this product.'), $inStock );
	}

	return '';
}

?>
