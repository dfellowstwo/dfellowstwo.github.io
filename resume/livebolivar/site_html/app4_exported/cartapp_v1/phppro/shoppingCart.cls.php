<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* All manipulations of Shopping Cart data.
*
* As of build 1896, all shipping and handling is treated as a total and presented as
* a seperate line item on the invoice. No shipping and/or handling costs are to be
* used on a per line-item bases.
*
* @version $Revision: 3008 $
* @author Cees de Gruijter
* @category SCC PRO
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

require CARTREVISION . '/php/shoppingCart.cls.php';

class ShoppingCartPro extends ShoppingCart {

	var $xtrShippingType = -1;
	var $weightrates = false;	// hash with rate->name => array( upper_weight => cost )
	var $coupon = '';			// coupon code

	function ShoppingCartPro ( $owner ) {

		parent::ShoppingCart( $owner );

		if( isset( $_POST['coupon'] ) && ! empty( $_POST['coupon'] ) )
			$this->coupon = $_POST['coupon'];
		else if( isset( $_SESSION['CouponCode'] ) )
			$this->coupon = $_SESSION['CouponCode'];
	}

 /*****************************  CART METHODS  *****************************/

	function saveCart ( ) {
	
		parent::saveCart();
		$_SESSION['CouponCode'] = $this->coupon;

	}


 /*****************************  NAME & DESCR  *****************************/


	function getSKU ( $cartid ) {
		if( !isset($this->Prods[$cartid]) || !isset($this->Prods[$cartid]['sku']) )	return '';
		return $this->Prods[$cartid]['sku'];
	}


 /*****************************  OPTIONS  *****************************/

	function getPriceOptions( $cartid )
	{
		// find the product and it's options
		$prdid = $this->getId( $cartid );
		$prod =& $this->owner->getProduct ( $prdid, false);
		$options =& $prod['options'];
		#echo "Cart ID: $cartid, Product ID: $prdid"; print_r($options);

		if( !$this->hasOptions( $cartid ) || empty( $options ) ) return 0.0;
		$sel = explode(',', $this->getOptions( $cartid ) );

		$extra_price = 0.0;
		for( $i = 0; $i < count($sel); $i++ ) {
			// if nothing selected AND options defined, then show first option as default
			if( $sel[$i] == -1 && count( $options[$i]['items'] ) > 0 ) {
				$sel[$i] = $options[$i]['items'][0]['value'];
			}
			foreach( $options[$i]['items'] as $item ) {
				if( $item['value'] == $sel[$i] ) {
					$extra_price += $item['price'];
					break;
				}
			}
		}
		return $extra_price;
	}

 /*****************************  PRICE & QUANTITY  *****************************/


	function getPrice ( $cid ) {

		if( !isset($this->Prods[$cid]) ) return 0;

		return $this->Prods[$cid]['price'] + $this->getPriceOptions( $cid );
 	}


	// returns price * qty per cart-id
 	function getSubtotalPriceProduct ( $cid, $withDiscount = false) {

		if( !isset($this->Prods[$cid]) ) return 0;
		#echo "ID: $productid, QTY: {$this->Prods[$cid]['qty']}, Price: {$this->Prods[$cid]['price']}";

		if( $withDiscount && isset( $this->Prods[$cid]['discount'] ) ) {
			return $this->Prods[$cid]['qty'] * $this->getPrice ( $cid ) - $this->Prods[$cid]['discount'];
		} else {
			return $this->Prods[$cid]['qty'] * $this->getPrice ( $cid );
		}
	}


 /*****************************  TAXES  *****************************/


	// tax amount for 1 product rounded to cents
 	function getTaxAmountProduct ( $cid ) {

		if( !isset($this->Prods[$cid]) ) {
			return 0;
		}

		$tax = 0;
		$prod_prc = $this->lookupTaxPerc( $this->Prods[$cid]['taxname'] );

		// Only applied the shipping per product in case the type of shipping is set manual
		if( $this->owner->getConfigS( 'shipping_calcmethod' ) == 'manual' )
		{
			$shippingtax = $this->lookupTaxPercShipping();
			if( $shippingtax ) {
				$tax += round( $shippingtax * $this->Prods[$cid]['handling'] );
				$tax += round( $shippingtax * $this->Prods[$cid]['shipping'] );
			}
		}


		// tax on product only is also used when type is not defined (-1)
		$tax += round( $prod_prc *  $this->getPrice( $cid ) );

		return $tax;
	}


	// tax amount on extra shipping, only has a value != 0 if $config['TaxRates']['Shipping']
	// exists for the location that the buyer is in.
 	function getTaxAmountExtraShipping ( ) {

		// If it weight method applied it will mean the qunatity will be global
		if( $this->owner->getConfigS( 'shipping_calcmethod' ) == 'weight' )
			return $this->lookupTaxPercShipping() * $this->getShippingHandlingTotal();
		else
		// Other wise we need to check in case the method is manual which kind of extrashipping is being applied
		return $this->lookupTaxPercShipping() *  $this->getExtraShippingAmount();

	}



 /*****************************  SHIPPING  *****************************/


 	 /* Shipping & Handling costs for all products together, calculation method
 	  * is determined by $config['shipping_calcmethod']. Allowed values are:
 	  *  - 'manual'		is what used to be 'extra-shipping'
 	  *  - 'weight'		uses the weigh parameter and a rates table
 	  *
 	  * If 'shipping_calcmethod' is not set, the 'manual' method is used for compatibility
 	  * with older versions.
 	  */
 	function getShippingHandlingTotal ( $method = false ) {

 		if( empty($this->Prods) ) return 0;

 		if( $method === false ) {
			$method = $this->getExtraShippingIndex( );
 		}

		if( $this->owner->getConfigS( 'shipping_calcmethod' ) == 'weight' ) {
			return $this->getShippingHandlingTotal_Weight( $method );
		}

 		return $this->getShippingHandlingTotal_Manual( $method );
 	}

 	 /* PRIVATE
 	  * Shipping & Handling costs for all products together:
 	  *  - Total is always: shipping_prod + handling_prod + minimim_charge + extra_charge
 	  *
  	  *  How to treat the number in $extrashipping array when type is:
	  *		-1 -> 0,
	  * 	 0 -> As a fixed amount,
	  *		 1 -> As a % over shipping&handling,
	  *		 2 -> As a fixed amount * number of individual products (not line items)
	  *		 3 -> As a % over shipping&handling * number of individual products (not line items)
	  *		 4 -> Fixed_Amount + ( Shipping_Product1 + Shipping_Product2 ) * Percentage_Rate
	  *
  	  * If no $method is specified, the actual extra-costs are return
  	  * else other costs can be calculated, e.g. to pass on to Google Checkout.
  	  */
 	function getShippingHandlingTotal_Manual ( $method = false ) {

		$amount = $this->getShippingProducts() +
				  $this->getHandlingProducts();

 		return $amount + $this->getExtraShippingAmount( $method );
 	}


 	 /* PRIVATE
 	  * Shipping & Handling costs for all products together:
	  *
  	  * If no $method is specified, the actual extra-costs are returned
  	  * else other costs can be calculated, e.g. to pass on to Google Checkout.
  	  */
	function getShippingHandlingTotal_Weight ( $method ) {

		if( ! $this->weightrates ) {
			$this->weightrates = $this->owner->getConfigS('ShippingRates');
		}

		if( $method === false )
			$method = $this->getExtraShippingIndex ( );

		$location = $this->getTaxLocationId();

		if( ! isset( $this->weightrates[ $location ] ) ||
			! isset( $this->weightrates[ $location ][ $method ] ) ) {
			return 0;
		} else {
			
			// check the weight ranges from small to larger, thus order the array
			// additional complexity, sort on number, not on string value
			$ranges =& $this->weightrates[ $location ][ $method ];
			ksort( $ranges, SORT_NUMERIC );
		}
		

		// give return value in cents
		$div = pow( 10, $this->owner->getConfigS('ShippingRatesDecimals') - 2 );
		$rangeDiv = pow( 10, $this->owner->getConfigS('ShippingRangeDecimals') );

		$weight = $this->getTotalWeightCart();
		
		$caseOutOfRange = 0;
		foreach( $ranges as $upper => $charge ) {

			switch ( $upper ) {

			case -1:
			case -2:
				// deal with the out-of-range value later
				$caseOutOfRange = $upper;
				break;

			default:
				// example: if upper = 5, then a weight upto and including 5 falls in that group
				if( $upper / $rangeDiv - $weight  >= 0 ) {
					return round( $charge / $div );
				}
			}
		}
		// we're here, the weight was outof range
		switch ( $caseOutOfRange ) {
			case -1: 			// the weight is more than is in the list, use this value
				return round( $charge / $div );

			case -2:			// the weight is too much for this method
				return false;
				
			default:
				writeErrorLog( 'getShippingHandlingTotal_Weight - unexpected weight range value: ' , $caseOutOfRange );
		}
	}
	// Returns the amount of extrashipping without taking account of products
	function getExtraShippingAmount( $method = false)
	{
		if( $method === false )
			$method = $this->getExtraShippingIndex( );

		if ( $method < 0 || $method >= count( $this->owner->extrashipping ) )
			return $this->getShippingMinimumCharge();
		else {
			$extra_type = $this->owner->extrashipping[$method]['type'];
			$extra_amount = $this->owner->extrashipping[$method]['amount'];
		}

		switch( $extra_type ) {
		case -1:
			$amount = $this->getShippingMinimumCharge();
			break;
		case 0:
			$amount = $this->getShippingMinimumCharge() + $extra_amount;
			break;

		case  1:
			$amount = $this->getShippingMinimumCharge() + ( $this->getShippingMinimumCharge() + $this->getShippingProducts() + $this->getHandlingProducts() ) * $extra_amount / 10000;
			break;

		case 2:
			$amount = $this->getShippingMinimumCharge() + $this->getNumberOfProducts() * $extra_amount;
			break;

		case 3:
			$amount = ( $this->getShippingMinimumCharge() + $this->getShippingProducts() + $this->getHandlingProducts() ) * $this->getNumberOfProducts() * $extra_amount / 10000;
			break;

		case 4:
			$amount =  $this->getShippingMinimumCharge() + ( $this->getShippingProducts() + $this->getHandlingProducts() ) * $extra_amount / 10000;
			break;
		}
		return $amount;
	}


 /*****************************  COUNTERS & TOTALS  *****************************/

	// calculate the total weight of the cart for all products that have
	// config['shipping_weightunit'] as weight unit.
	function getTotalWeightCart ( ) {

  		if( empty($this->Prods) ) return 0;

  		$weight = 0;
		$wunit = $this->owner->getConfigS( 'shipping_weightunit' );

	 	foreach( $this->Prods as $cid=>$p ) {

	 		if( $p['weightunits'] == $wunit ) {
	   			$weight += ( $p['weight'] ? $p['weight'] : 0 ) * $p['qty'];
	 		}
 		}

		return $weight;
	}


 /****************************  PROMOTION DISCOUNTS  ****************************/

	function getCouponCode ( ) {
		return strtoupper( $this->coupon );
	}


	function getPromoDiscount ( ) {

		global $promos;
		global $promo_combined;
		
		if( ! $promos ) return -1;

		// reset the discounts in the cart
		$discounts = array();						// discount on cart as a whole
	 	foreach( $this->Prods as $cid=>$p ) {
   			$p['discount'] = 0;						// discount per line item
 		}

		if( $promo_combined ) {
				
			// apply the promotions to fill the discounts
			foreach( $promos as $name => $promo )
			{
				$discounts[] = applyPromo ( $this, $promo );
			}
			
			// apply the coupon code
			$discounts[] = applyCoupon ( $this, $this->coupon );		
			
			// add total of all lines
			foreach( $this->Prods as $cid=>$p ) {
   				$discounts[] = $p['discount'];
			}
			return empty( $discounts ) ? 0 : array_sum( $discounts );
 
		} else {
			
			foreach( $promos as $name => $promo ) {
	
				$d = applyPromo ( $this, $promo );

				// add total of all lines reset the ['discount'] because they are not combined
				foreach( $this->Prods as $cid=>$p ) {
   					$d += $p['discount'];
		   			$p['discount'] = 0;
				}
				$discounts[] = $d;				
			}
			
			// apply the coupon code
			$d = applyCoupon ( $this, $this->coupon );		
			foreach( $this->Prods as $cid=>$p ) {
   				$d += $p['discount'];
		   		$p['discount'] = 0;
			}
			$discounts[] = $d;				

			return empty( $discounts ) ? 0 : max( $discounts );
		}
	}
}
?>
