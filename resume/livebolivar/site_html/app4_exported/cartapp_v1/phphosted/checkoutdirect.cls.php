<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
*	Some relevant info from PP
*
*		For UK, only Maestro, Solo, MasterCard, Discover, and Visa are allowable. For Canada, only MasterCard and Visa are
*		allowable; Interac debit cards are not supported. NOTE:If the credit card type is Maestro or Solo, the CURRENCYCODE 
*		must be GBP. In addition, either STARTDATE or ISSUENUMBER must be specified.
*
*
* @version $Revision: 2990 $
* @author Cees de Gruijter
* @category SCC HOSTED
* @copyright Copyright (c) 2010 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

require 'php/checkoutpp.cls.php';


class CheckoutDirect extends Checkout
{
	// required fields
	var	$reqflds = array('country',
						'cardtype',
						'cardnum',
						'expmonth',
						'expyear',
						'csc', 
						'firstname',
						'lastname',
						'billingaddr1',
					//	'billingaddr2',
						'billingcity',
						'billingstate',
						'billingzip',
					//	'billingphone',
						'billingemail',
						'shippingname',
						'shippingaddr1',
					//	'shippingaddr2',
						'shippingcity',
						'shippingstate',
						'shippingzip'
					//	'shippingphone',
					//	'shippingemail'
						);

	function CheckoutDirect ( &$page ) {
		parent::Checkout( $page );
		$this->gatewayName = 'PayPalDirect';
	}


	function checkReqFields ( &$msg ) {
		
		foreach( $this->reqflds as $key ) {
			if( ! isset( $_POST[ $key ] ) || empty( $_POST[ $key ] ) ) {
				if( strpos($key, 'ship') === false ||
					( isset( $_POST[ 'add_shipping' ] ) && ! isset( $_POST[ 'use_billing' ] ) ) ) {
					$msg .= 'Required field missing';
					break;
				}
			}
		}
		return empty( $msg );
	}
	
	// return true or false when response is empty or response instead of true when it is defined
	function isReqField ( $key, $response = '' ) {
		$req = in_array( $key, $this->reqflds ) && ( strpos($key, 'ship') === false || isset( $_POST[ 'add_shipping' ] ) ); 
		if( empty( $response ) )			return $req;
		else 								return $req ? $response : '';
	}
	
	// check that at least the constraints imposed by PayPal are met
	function checkSanityFields ( &$msg ) {

		$types = array( 'Visa', 'MasterCard', 'Discover', 'Amex', 'Maestro', 'Solo' );
 
		if( ! in_array( $_POST['cardtype'], $types ) ) {
			$msg = _T('Card type is not valid.');
			return false;
		}
		
		if( ! preg_match ( '/\d{13,16}/', $_POST['cardnum'] ) ) {
			$msg = _T('Card number contains other characters than digits or doesnot have the correct length.');
			return false;
		}
		
		if( ! preg_match ( '/\d{4}/', $_POST['expyear'] ) ||
			! preg_match ( '/\d{2}/', $_POST['expmonth'] ) ) {
			$msg = _T('Card expiration date must be formated like this: mm/yyyy.');
			return false;
		}
		
		if( ! preg_match ( '/\d{3,4}/', $_POST['csc'] ) ) {
			$msg = _T('CSC should be 3 or for digits only.');
			return false;
		}
		
		if( strlen( $_POST['firstname'] ) > 25 ||
			strlen( $_POST['lastname'] ) > 25 ) {
			$msg = _T('First and Last names are required and should not be longer than 25 characters.');
			return false;
		}
		
		if( strlen( $_POST['shippingname'] ) > 32 ) {
			$msg = _T('Shipping Name longer than 32 characters.');
			return false;
		}

		if( strlen( $_POST['billingaddr1'] ) > 100 ||
			strlen( $_POST['billingaddr2'] ) > 100 ||
			strlen( $_POST['shippingaddr1'] ) > 100 ||
			strlen( $_POST['shippingaddr2'] ) > 100 ) {
			$msg = _T('One of the address lines is longer than 100 characters.');
			return false;
		}

		if( strlen( $_POST['billingcity'] ) > 40 ||
			strlen( $_POST['shippingcity'] ) > 40 ) {
			$msg = _T('A city name is longer  than 40 characters.');
			return false;
		}

		if( strlen( $_POST['billingzip'] ) > 20 ||
			strlen( $_POST['shippingzip'] ) > 20 ) {
			$msg = _T('Zip codes should shorter than 20 characters.');
			return false;
		}
		
		if( strlen( $_POST['billingstate'] ) > 40 ||
			strlen( $_POST['shippingstate'] ) > 40 ) {
			$msg = _T('States should shorter than 40 characters.');
			return false;
		}

		return true;
	}
	
	
	function doDirectPayment ( ) {

		// some data validation
		if( $this->cart->getGrandTotalCart() == 0 ||
			$this->page->getConfigS('shopcurrency') == "" )
			return "FAILURE";

		// new transaction start - clear any old data and lock the cart
		$this->clearSessionData();
		$this->page->lockCart( true );

		$nvpstr = '&PAYMENTACTION=Sale'
		 		. '&IPADDRESS=' . $_SERVER['REMOTE_ADDR']
				. '&CREDITCARDTYPE=' . $_POST['cardtype']
				. '&ACCT=' . $_POST['cardnum']
				. '&EXPDATE=' . $_POST['expmonth'] . $_POST['expyear'] 
				. '&CVV2=' . $_POST['csc']
				. '&FIRSTNAME=' . urlencode( $_POST['firstname'] )
				. '&LASTNAME=' . urlencode( $_POST['lastname'] )
				. '&STREET=' . urlencode( $_POST['billingaddr1'] )
				. '&CITY=' . urlencode( $_POST['billingcity'] )
				. '&STATE=' . urlencode( $_POST['billingstate'] )
				. '&ZIP=' . urlencode( $_POST['billingzip'] ) 
				. '&COUNTRYCODE=' . $this->page->getConfigS( 'PPCountryCodes', $_POST['country'] ) 
				. '&PAYMENTREQUEST_0_CURRENCYCODE=' . $this->page->getConfigS('shopcurrency');

		if( !empty( $_POST['billingaddr2'] ) )		
			$nvpstr	.= '&STREET2=' . urlencode( $_POST['billingaddr2'] );
				
		// add shipping address if needed
		if( isset( $_POST[ 'add_shipping' ] ) && ! isset( $_POST[ 'use_billing' ] ) ) {
			$nvpstr .= '&SHIPTONAME=' . urlencode( $_POST['shippingname'] )
					. '&SHIPTOSTREET=' . urlencode( $_POST['shippingaddr1'] )
					. '&SHIPTOCITY=' . urlencode( $_POST['shippingity'] )
					. '&SHIPTOSTATE=' . urlencode( $_POST['shippingstate'] )
					. '&SHIPTOZIP=' . urlencode( $_POST['shippingzip'] )
					. '&SHIPTOCOUNTRY=' . $this->page->getConfigS( 'PPCountryCodes', $_POST['country'] );

			if( !empty( $_POST['shippingaddr2'] ) )		
				$nvpstr	.= '&SHIPTOSTREET2=' . urlencode( $_POST['shippingaddr2'] );

			if( !empty( $_POST['shippingphone'] ) )		
				$nvpstr	.= '&SHIPTOPHONENUM=' . urlencode( $_POST['shippingphone'] );

		} 

		if( $this->page->getConfigS( 'transaction_log' ) ) {
			$nvpstr .= '&PAYMENTREQUEST_0_NOTIFYURL=' . urlencode( $this->page->getFullUrl( 'servicepp.php', false, true ) );
		}

		// add line items and totals
		$nvpstr .= '&AMT=' . urlencode( number_format( $this->cart->getGrandTotalCart() / 100, 2, '.', '') );
		$nvpstr .= '&ITEMAMT=' . urlencode( number_format( ( $this->cart->getSubtotalPriceCart() + $this->cart->getShippingHandlingTotal() ) / 100, 2, '.', '' ) );
		$nvpstr .= '&TAXAMT=' . urlencode( number_format( $this->cart->getTotalTax() / 100, 2, '.', '') );

		// strip some stuff because it isn't support in this api
		$this->addLineItems( $tmp );
		$nvpstr .= str_replace( 'PAYMENTREQUEST_0_', '', $tmp);

		// add reference to our transaction log
		$ref = '';
		if( $this->page->GetTransCode( $ref ) )
			$ref = '&CUSTOM=' . $ref;

		$this->hash_call("DoDirectPayment", $nvpstr );
		$success = ( strtoupper( $this->resArray["ACK"] ) == 'SUCCESS' );

		if( ! $success ) {
			writeErrorLog( 'DirectCheckout NVP', $nvpstr );
			writeErrorLog( 'DirectCheckout Response', $this->resArray );
		}
			
		return $success;
	}

}
