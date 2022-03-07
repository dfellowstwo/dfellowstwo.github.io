<?php

/**
 *	This interface defines all methods that the Checkout classes for the various payment
 *  gateways are expected to have.
 *
 */
abstract class CheckoutClassInterface
{
	protected $returnUrl = false;
	protected $cancelUrl = false;
	protected $cart;
	protected $page;

	function __construct ( $page ) {

		$this->page = $page;
		$this->cart = $page->getCartInstance();
	}

	abstract function getCheckoutFields ( );

	public function doCheckOut ( ) {
	}

	public function setReturnUrl ( $url ) {
		$this->returnUrl = $url;
	}

	public function setCancelUrl ( $url ) {
		$this->cancelUrl = $url;
	}

}





// html safe encoding to a certain max length
function maxLenEncode( $string, $maxlength = -1 ) {

	$string = htmlspecialchars( $string, ENT_NOQUOTES );

	if( $maxlength > 0 && strlen( $string ) > $maxlength ) {

		$string = substr( $string, 0, $maxlength );

		if( false !== ($p = strrpos( $string, '&') ) ) {

			$string = substr( $string, 0, $p );

		}
	}

	return $string;
}


?>