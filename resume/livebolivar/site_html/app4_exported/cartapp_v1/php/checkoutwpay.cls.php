<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Extension of Page for checking out with WorldPay.
*
*
* @version $Revision: 2472 $
* @author Cees de Gruijter
* @category SCC PRO
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

require_once CARTREVISION . '/api/checkoutclass.interface.php';


class CheckoutWPay extends CheckoutClassInterface {

	function __construct ( $page ) {

		parent::__construct( $page );
	}


	// return false if no transaction logging found
	function getCheckoutFields ( ) {

    	if( ! isset( $_SESSION[ORDERREFKEY] ) || empty( $_SESSION[ORDERREFKEY] ) ) {
			return false;
    	}

		// make sure the cart stays locked
		$this->page->lockCart( true );

		$fields = '';
		$this->addMessageToMessage( $fields );
		$this->addHeader( $fields );

		#print_r($fields);
		return $fields;

	}

	/*********************** PRIVATE METHODS *********************************/

	function addMessageToMessage ( &$fields ) {

		// there are max 255 characters available to describe the purchase
		$descr = _T( 'Your purchase of:  ');

		foreach( $this->cart->getPairProductIdGroupId() as $article ) {

			$cid =& $article['cartid'];

			$descr .= $this->cart->getName( $cid, true ) . ', ';
		}

		// replace last ',' with a '.'
		$descr = trim( $descr, ' ,') . '.';

		// add extra shipping as line-item too
		$es_amount = $this->cart->getShippingHandlingTotal();
		if( $es_amount != 0 ) {
			$descr .= ' ' . $this->page->getExtraShipping( $this->page->getExtraShippingIndex() ) . '.';
		}

		$fields .= '<input type="hidden" name="desc" value="'  . maxLenEncode( $descr, 255 ) . '" />';

	}


	function addHeader ( &$fields )
	{
		$instId = $this->page->getConfigS( 'WorldPay', 'ID' );
		$cartId = strtoupper( $_SESSION[ORDERREFKEY] );
		$amount = number_format( $this->cart->getGrandTotalCart() / 100, 2, '.', '' );
		$currency = $this->page->getConfigS('shopcurrency');
		$check = md5( obf( $this->page->getConfigS( 'WorldPay', 'SECRET' ) ) . ':'. $amount . ':' . $currency . ':' . $cartId . ':' . $instId );

		$fields .= '<input type="hidden" name="instId" value="'  . $instId . '" />'
				 . '<input type="hidden" name="cartId" value="' . $cartId . '" />'
				 . '<input type="hidden" name="amount" value="' . $amount . '" />'
				 . '<input type="hidden" name="currency" value="' . $currency . '" />'
				 . '<input type="hidden" name="signatureFields" value="amount:currency:cartId:instId">'
				 . '<input type="hidden" name="signature" value="' . $check . '">';

		// make sure the url where the form is submitted is also set to the test url!
		if( $this->page->getConfigS('WorldPay', 'TEST_MODE') ) {
			$fields .= '<input type="hidden" name="testMode" value="100" />';
			$fields .= '<input type="hidden" name="name" value="AUTHORISED" />';		// can be set to REFUSED, AUTHORISED, ERROR or CAPTURED to get those test responses
    	}
	}


}


?>