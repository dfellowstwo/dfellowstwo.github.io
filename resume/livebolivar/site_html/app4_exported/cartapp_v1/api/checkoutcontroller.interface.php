<?php

/**
 *	This interface defines all methods that the ShoppingCart class expects to find
 *	in the class that creates a ShoppingCart instance and refers to as "owner".
 *
 */
interface CheckoutControllerInterface
{
	/**
	 * Get the configuration that is defined for $param1 and (optional) secondary selector $param2
	 *
	 * @param 	$param1		primary selector for a configuration name-value pair
	 * @param 	$param2		optional secondary selector for a configuration name-value pair
	 * @return false if no value is found for the specified selectors.
	 */
	public function getConfigS ( $param1, $param2 = false );

	/**
	 * Get a product definition
	 *
	 * @param 	$productid		selector for the product to return
	 * @param 	$formatted		determines if the output is the raw data or data made suitable for presentation
	 * @return false if no product found, else a ProductProps object instance.
	 */
	public function getProduct ( $productid, $formated = true );

	/**
	 * Get the default tax location id
	 *
	 * @return -1 if no default id exists
	 */
	public function getDefaultTaxLocationId ( );


	/**
	 * Get an array with all extra-shipping definitions as defined in SCC
	 * format:   $extrashippings[] = array( 'description' => 'One Dollar Shipping',
	 *										'amount' => '100',
	 *										'type' => '-1',
	 *										'show' => true,
	 *										'id' =>  '0' );
	 * @return null if no extra-shipping is used.
	 */
	public function getExtraShippingDefinitions ( );

	/**
	 * Get all extra-shipping descriptions for display purposes
	 *
	 * @return array( array( 'id' => 'description' ), ... ), array may be empty
	 */
	public function getExtraShippingList ( );


	/**
	 * Get an extra-shipping description for display purposes
	 *
	 * @param	$index			optional selector to request a specific extra-shipping item
	 * @return the 'description' of that single item or false if not found
	 */
	public function getExtraShippingDescr ( $index );

	/**
	 * Get an extra-shipping costs for cart calculations
	 *
	 * @param	$index			optional selector to request a specific extra-shipping item
	 * @return array( type, amount )
	 */
	public function getExtraShippingCosts ( $index );


	/**
	 * Payment processor classes call this when they need to inform the user, mostly
	 * about errors that occurred
	 *
	 * @param	$msg				the message to show
	 * @return nothing
	 */
	public function setCartMessage ( $msg );


	/**
	 * Get reference to the cart instance.
	 *
	 * @return reference to the cart instance
	 */
	public function getCartInstance ( );

	/**
	 * Lock or unlock the cart for all other operation from the same session.
	 *
	 * @param	$lock		Lock when true, unlock when false
	 * @return nothing
	 */
	public function lockCart( $lock );

	/**
	 * Get URL to a shop logo image.
	 *
	 * @return full URL to an image or empty string if none defined
	 */
	public function getShopLogoUrl ( );

	/**
	 * Get URL to the Transaction Logger.
	 *
	 * @return full URL to the logger or empty string if none defined
	 */
	public function getTransactionLogUrl ( );

	/**
	 * Save the cart contents in a database.
	 *
	 * @return TransactionID (also refered to as route) or false on failure
	 */
	public function saveCartToDB ( );
}


/**
 *	Definition of the minimum properties and methods a product object needs to have.
 *
 */
abstract class ProductProps
{
	public $productid;
	public $name;
	public $shortdescription;
	public $weight = 0;
	public $weightdigits = 1;
	public $weightunits = '';
	public $sku = '';
	public $yourprice = 0;
	public $tax = 0;
	public $shipping = 0;
	public $handling = 0;

	/**
	 * Get an OptionItem by index
	 *
	 * @return null if no ProductOptionItemProps exists by that index
	 */
	abstract public function getOptionItemsByIndex ( $index );
	abstract public function getDefaultOptionValue ( $index );

	/*
	 * @return list( $type, $amount )
	 */
	abstract public function getExtraShipping( $method );
	
	public function getWeight ( ) {
		return $this->weight / pow( 10, $this->weightdigits );
	}
}


abstract class ProductOptionProps
{
	public $name;
	public $items;							///< array of ProductOptionItems
}


abstract class ProductOptionItemProps
{
	public $label;
	public $price;
	public $value;
	public $selected;
}


/* by setting $divider to 100, eg 1000 -> 10,00
 * WARNING: input may not contain decimals of cents when working with cents!
 */
function formatMoney ( $amount, $divider = 1 ) {
	if (is_float($amount) && $divider == 100) {
		$amount = round($amount);
	}
	$amount = moneyToFloat($amount);
	if (!$amount) return '';
	else $amount = $amount / $divider;
	return formatAmount($amount, 2);
}


function intToMoney ( $amount, $divider = 100 ) {
	formatAmount( (float)$amount / $divider, 2);
	if (!$amount)
		return '';
	else
		return formatAmount( (float)$amount / $divider, 2);
}


// split (almost) any money format into whole units and cents
function moneyToFloat ( $input ) {
	$re = '/^((?:[,.\s]?\d{1,3})+?)(?:[.,](\d\d))?$/';
	if (preg_match($re, trim($input), $match)) {
		$money = str_replace(array(',','.',''), '', $match[1])
        	   . '.' . (!isset($match[2]) || $match[2] == '' ? '00' : $match[2]);
	} else $money = '';
	return $money;
}


function moneyToInt ( $input ) {
	return moneyToFloat($input) * 100;
}


function formatAmount ( $number, $dec_places, $lng = 'en' ) {
	switch ($lng) {
	case 'en':
		$r = number_format($number, $dec_places, '.', ',');
		break;
	case 'nl':
	case 'es':
		$r = number_format($number, $dec_places, ',', '.');
		break;
	case 'fr':
		$r = number_format($number, $dec_places, ',', ' ');
		break;
	default:
		die("No definition found for language '{$lng}' in formatAmount.");
    }
	return $r;
}


function formatDateTime ( $udate, $lng, $fmt = '' ) {
	switch ($lng) {
	case 'nl':
		setlocale(LC_TIME, 'nl_NL');
		break;
	case 'es':
		setlocale(LC_TIME, 'es_ES');
		break;
	case 'en':
	default:
	setlocale(LC_TIME, 'en_eng');
	}
	if ($fmt)
		return strftime($fmt, $udate);
	else
		return strftime("%A, %d-%B-%Y %T", $udate);
}











?>
