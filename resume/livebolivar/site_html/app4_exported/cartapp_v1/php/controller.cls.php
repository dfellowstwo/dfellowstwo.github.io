<?php
/**
 * CoffeeCup Software's Shopping Cart Creator.
 *
 * Required POST parameters:
 *  	method, value can be: add, update, remove
 * or one of the following:
 * 		recalculate
 * 		delete
 * 		emptycart
 * 		checkout
 * 		confirmpp
 * 		paypalcheckout
 * 		googlecheckout
 *
 *  Optional parameters:
 * - item_id
 * - group_id
 * - any other form field that is appropriate in a certain context
 *
 * Upon successful cart update, the user is redirected to the cart
 * (see NAVIGATION comment)
 *
 * @version $Revision: 3332 $
 * @author Cees de Gruijter
 * @category SCC
 * @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
 */

// load static functions to manipulate cart
require 'cartcontrol.inc.php';

if( $myPage->message != '' ) {
	return;
}

// careful not to overwrite what earlier includes defined
if( empty( $controllerClassName ) ) $controllerClassName = 'Controller';


class Controller {
	
	function Controller ( ) {

		// cleanup any cart message that shouldn't be there'
		if( isset( $_SESSION['cart_warning'] ) ) {
			unset( $_SESSION['cart_warning'] );
		}
	}
	
	// What do we need to do? Parameters not handled here can be handled by the page that dous the submit
	function Dispatch ( ) {

		global $myPage;

		if( isset($_POST['method']) ) {
			$method = $_POST['method'];
		} else if( isset($_POST['recalculate']) ) {
			$method = 'update';
		} else if( isset($_POST['delete']) ) {
			$method = 'remove';
		} else if( isset($_POST['emptycart']) ) {
			$method = 'emptycart';
		} else if( isset($_POST['paypalcheckout']) || isset($_POST['paypalcheckout_x']) ) {
			$method = 'cc_paypalcheckout';
		} else if( isset($_POST['paypalwpscheckout']) || isset($_POST['paypalwpscheckout_x']) ) {
			$method = 'cc_paypalwpscheckout';
		} else if( isset($_POST['googlecheckout']) || isset($_POST['googlecheckout_x']) ) {
			$method = 'cc_googlecheckout';
		} else if( isset($_POST['anscheckout']) || isset($_POST['anscheckout_x']) ) {
			$method = 'cc_anscheckout';
		} else if( isset($_POST['twocheckout']) || isset($_POST['twocheckout_x']) ) {
			$method = 'cc_2checkout';
		} else {
			$method = false;
		}
		
		if( $method ) {
			switch( $method ) {
				case 'emptycart':
					$errorMsg = $myPage->emptyCart();
					$this->ClearPaymentState();
					break;

				case 'add':
				case 'update':
				case 'remove':
					$this->ClearPaymentState();
					$mytask = 'cart_' . $method;
					$errorMsg = $mytask( $cids );
					break;

				case 'cc_googlecheckout':
				case 'cc_paypalcheckout':
				case 'cc_paypalwpscheckout':
				case 'cc_anscheckout':
				case 'cc_2checkout':

					if( count( $myPage->cart->Prods ) > 0 ) {

						// Check if the location and shipping is properly establish
						$errorMsg = $this->CheckShippingLocation();
						if( $errorMsg )	{
							break;
						}
						header('Location: ' . $myPage->getConfig($method, true) );
						exit(0);

					} else
						cart_update();	// does nothing that can cause problem

					break;

				default:
					die( sprintf( _T("Could not recognize task in Post request (%s)."), $method ) );
			}

			if( $errorMsg ) {
				
				$myPage->setCartMessage( $errorMsg );

			} else {

				$myPage->saveCart();

				// NAVIGATION: stop output buffering and redirect to cart if needed
				if( stristr( strtolower($_SERVER['SCRIPT_NAME']), 'cart.php' ) === false &&
					$myPage->getConfigS('navigate_stayonpage') == false )
				{
					if( ob_get_level() != 0 ) ob_end_clean();
					header("Location: cart.php");
					exit();
				}
			}
		}
	}


	// Return an error message in case the Location or the Shipping is not established
	function CheckShippingLocation() {

		global $myPage;
		$msg = '';

		// do this after the location, because location is used for weight based shipping costs
		if( isset( $_POST['extrashipping'] ) ) {

			if( $_POST['extrashipping'] == -1 && count($myPage->getExtraShipping() ) > 1 )
			{
				$msg = $msg . _T('Please choose a shipping method.<br />');
			}
		}

		if( isset($_POST['taxlocation']) ) {
			if( $_POST['taxlocation'] == -1 && count($myPage->getTaxLocations()) > 1 )
			{
				$msg = $msg . _T('Please choose a shipping destination.<br />');
			}
	
		}

		if( $msg != '' ) 
			return $msg;
		else
			return false;
	}
	
	function ClearPaymentState ( ) {

		// remove any pending authorisation with PayPal, it is invalidated by changes to the cart
		if( isset($_SESSION[PAYMENT]) ) {
			unset( $_SESSION[PAYMENT] );
			$myPage->resArray = array();
		}
	}

}

?>
