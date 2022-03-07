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
 * @version $Revision: 2737 $
 * @author Cees de Gruijter
 * @category SCC PRO
 * @copyright Copyright (c) 2010 CoffeeCup Software, Inc. (https://www.coffeecup.com/)
 */


// careful not to overwrite what earlier includes defined
if( empty( $controllerClassName ) ) $controllerClassName = 'ProController';

require CARTREVISION . '/php/controller.cls.php';

class ProController extends Controller {
	
	function ProController ( ) {
		parent::Controller();
	}
	
	// what do we need to do?
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
		} else if( isset($_POST['paypaldirect']) || isset($_POST['paypaldirect_x']) ) {
			$method = 'cc_paypaldirect';
		} else if( isset($_POST['googlecheckout']) || isset($_POST['googlecheckout_x']) ) {
			$method = 'cc_googlecheckout';
		} else if( isset($_POST['anscheckout']) || isset($_POST['anscheckout_x']) ) {
			$method = 'cc_anscheckout';
		} else if( isset($_POST['2cocheckout']) || isset($_POST['2cocheckout_x']) ) {
			$method = 'cc_2checkout';
		} else if( isset($_POST['worldpaycheckout']) || isset($_POST['worldpaycheckout_x']) ) {
			$method = 'cc_worldpay';
		} else {
			$method = false;
		}

		$errorMsg = false;

		if( $method ) {
			switch( $method ) {
			case 'emptycart':
				$errorMsg = $myPage->emptyCart();
				break;

			case 'add':
			case 'update':
			case 'remove':
				$mytask = 'cart_' . $method;
				$errorMsg = $mytask( $cids );
				break;

			case 'cc_googlecheckout':
			case 'cc_paypalcheckout':
			case 'cc_paypalwpscheckout':
			case 'cc_paypaldirect':
			case 'cc_anscheckout':
			case 'cc_2checkout':
			case 'cc_worldpay':

				if( count( $myPage->cart->Prods ) > 0 ) {

					// Check if the location and shipping is properly establish
					$errorMsg = $this->CheckShippingLocation();
					if( $errorMsg )	{
						break;
					}

					// double check that the requested articles haven't been sold in the mean time
					// and store the order in the log if possible before redirecting
					if( $this->CheckAvailability( $errorMsg ) && $this->SaveOrder( $method ) ) {

						if( $myPage->sdrive_stats ) {
							$sr = new StatsReporter( $myPage );
							$sr->NotifyCheckoutAttempt ( substr( $method, 3 ) );
						}
						
						$tmp = $myPage->getConfig( $method, true );
						header('Location: ' . $myPage->getConfig( $method, true ) );
						exit(0);
					}

				} else
					cart_update();	// does nothing that can cause problems

				break;

			case 'search':

				if( isset( $_POST['search_words'] ) && trim( $_POST['search_words'] ) != '' ) {
					header('Location: ' . $myPage->getConfig( 'cc_search', $_POST['search_words'] ) );
					exit(0);
				} else {
					$errorMsg = _T( "Please first type words to search for and then click on \"Search\"." );
				}
				break;

			default:
				echo( _T('Could not recognize task in Post request') . ' (' . $method . ').');
				writeErrorLog( __FILE__  . ' - Method in Post request not recognized:', $method);
				exit();
			}

			if( $errorMsg ) {
				$myPage->setCartMessage( $errorMsg );
			} else {

				$myPage->saveCart();

				// NAVIGATION: stop output buffering and redirect to cart if needed
				if( stristr( strtolower( $_SERVER['SCRIPT_NAME'] ), 'cart.php' ) === false &&
					$myPage->getConfigS( 'navigate_stayonpage' ) == false )
				{
					if( ob_get_level() != 0 ) ob_end_clean();
					header( 'Location: cart.php' );
					exit();
				}
			}
		}
	}

	// return true when products are available
	function CheckAvailability( &$msg ) {

		global $myPage;
		global $absPath;

		if( ! $myPage->getConfigS( 'track_stock' ) ) {
			return true;		// won't check, but not out-of-stock either
		}

		$prod_descr = '';
		if( ! $myPage->verifyAvailability( $prod_descr ) ) {
			$msg = sprintf( _T("We are very sorry, but the last \"%s\" has just been sold."), $prod_descr );
			return false;
		}

		return true;
	}


	function SaveOrder ( $gateway = '') {

		global $absPath;
		global $myPage;

		if( ! $myPage->getConfigS( 'transaction_log' )  ) {
			return true;		// won't store, but not really an error either
		}

		$cart =& $myPage->cart->exportCart();

		$cart[ 'gateway' ] = $gateway;
		$cart[ 'status' ] = 'Sending';

		// some follow up actions might need to be blocked if in test mode
		$cart[ 'testmode' ] = $myPage->getConfigS( $myPage->getGatewayName( $gateway ), 'TEST_MODE' );

		if( isset( $_SESSION[ ORDERREFKEY ] ) ) {
			$route = $_SESSION[ ORDERREFKEY ];
		} else {
			$route = false;
		}

		$updateStockBeforeTrans = $myPage->getConfigS( 'track_stock' ) == 'before';
		if( ! $myPage->saveTransactionData( $cart, $route, false, $updateStockBeforeTrans ) ) {
			$myPage->setCartMessage( 'Error while storing data: ' . $myPage->getDataMessage() );
			return false;		// won't store, but now something doesn't work as it should
		}

		$_SESSION[ ORDERREFKEY ] = $route;

		return true;			// success
	}
}
?>
