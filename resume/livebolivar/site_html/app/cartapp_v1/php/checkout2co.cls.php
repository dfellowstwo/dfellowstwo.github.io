<?php
/*
 *
 */

require_once CARTREVISION . '/php/utilities.inc.php';
require_once CARTREVISION . '/api/checkoutclass.interface.php';

class Checkout2CO extends CheckoutClassInterface {

	function __construct ( $page ) {

		parent::__construct( $page );
	}

	function getCheckoutFields ( ) {

		// make sure the cart stays locked
		$this->page->lockCart( true );

		$fields = '';
		$this->addHeader( $fields );
		$this->addProductsToMessage( $fields );

		#print_r($fields);
		return $fields;

	}



	/*********************** PRIVATE METHODS *********************************/



	function addHeader ( &$fields )
	{
		// prepare info that is independent from cart contents
		$amount = number_format( $this->cart->getGrandTotalCart() / 100, 2, '.', '' );

		if( isset( $_SESSION[ORDERREFKEY] ) && ! empty( $_SESSION[ORDERREFKEY] ) ) {
			$uniqueId = strtoupper( $_SESSION[ORDERREFKEY] );
		} else {
			// generate a unique transaction id based on time and some random addition
			$uniqueId = time() . '-' . rand( 100, 999 );
		}

		$fields .= '<input type="hidden" name="sid" value="' . $this->page->getConfigS('2CO', 'VENDOR_NUMBER') . '" />'
				 . '<input type="hidden" name="total" value="' . $amount. '" />'
    			 . '<input type="hidden" name="cart_order_id" value="' . $uniqueId . '" />'
    			 . '<input type="hidden" name="pay_method" value="CC" />'				// only CC, we have PayPal already
    			 . '<input type="hidden" name="skip_landing" value="1" />'
    			 . '<input type="hidden" name="x_receipt_link_url" value="'
    			 . getFullUrl( $this->page->getConfigS( '2CO', 'cc_2relay' ) , false )
    			 . '" />'
    			 ;

		if( $this->page->getConfigS( '2CO', 'TEST_MODE' ) == 1 ) {
			$fields .= '<input type="hidden" name="demo" value="Y" />';
		}


	}

	/* format: x_line_item = ID <|> name | description <|> quantity <|> unit price <|> taxable */
	function addProductsToMessage ( &$fields ) {

		// 1 = system specified id's, 2 = vendor specified id's, must be 1 or products are not shown
		$fields .= '<input type="hidden" name="id_type" value="1" />';

		// index for field names
		$idx = 1;

		foreach( $this->cart->getPairProductIdGroupId() as $article ) {

			$cid =& $article['cartid'];

			$optionstxt = maxLenEncode( $this->cart->getOptionsAsText( $cid, ' / ' ) );
			$descr = $this->cart->getDescr( $cid );

			// ensure the description + option text is not too long
			if( $this->cart->getName( $cid, true ) == $descr )
				$descr = $optionstxt;
			else
				$descr = maxLenEncode( $descr, 255 - strlen( $optionstxt ) ) . $optionstxt;

			// create some sort of product id
			$id = $this->cart->getProductProperty( $cid, 'sku' );
			if( empty( $id) ) {
				$id = $this->cart->getId( $cid );
			}

			$fields .= '<input type="hidden" name="c_prod_' . $idx . '" value="'
				  . maxLenEncode( $id, 31 )
				  . ','
				  . $this->cart->getUnitsOfProduct( $cid )
				  . '" />';
			$fields .= '<input type="hidden" name="c_name_' . $idx . '" value="'
				  . maxLenEncode( $this->cart->getName( $cid, true ), 128 )
				  . '" />';
			$fields .= '<input type="hidden" name="c_description_' . $idx . '" value="'
				  . $descr
				  . '" />';
			$fields .= '<input type="hidden" name="c_price_' . $idx . '" value="'
				  . number_format( $this->cart->getPrice( $cid ) / 100, 2, '.', '' )
				  . '" />';

			$idx = $idx + 1;
		}

		// add extra shipping as line-item too
		$es_amount = $this->cart->getShippingHandlingTotal();
		if( $es_amount != 0 ) {
			$fields .= '<input type="hidden" name="c_prod_' . $idx . '" value="XS' . $this->page->getExtraShippingIndex() . '" />';
			$fields .= '<input type="hidden" name="c_name_' . $idx . '" value="Shipping option" />';
			$fields .= '<input type="hidden" name="c_description_' . $idx . '" value="'
					 . $this->page->getExtraShipping( $this->page->getExtraShippingIndex() )
					 . '" />';
			$fields .= '<input type="hidden" name="c_price_' . $idx . '" value="'
					 . number_format( $es_amount / 100, 2, '.', '' )
					 . '" />';

			$idx = $idx + 1;
		}

	}


}

?>
