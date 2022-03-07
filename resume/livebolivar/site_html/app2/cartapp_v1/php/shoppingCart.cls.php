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
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (https://www.coffeecup.com/)
*/

define('SEP', 'v');

class ShoppingCart {
	
	/* data structure:
	 * $Prods[cartid] = array('name'=>..., 'price'=>..., 'options'=>...   etc)
	 *    options are stored as a string with option values that can be exploded for searching
	 * Products that differ in options are added as different products, to this end
	 *    a sequence number is added to the id, e.g.   12  12-1    12-2 ...
	 * Products must exist before any of the modify functions do something.
	 * Products that already exist can not be added, only modified and deleted.
	 *
	 * Notes:
	 * 	  $cartid is a string and the template sometimes adds spaces - use TRIM!
	 *    Beware of the 2 kinds of discounts: the amounts applied to a product and line items with a negative amount
	 */
	var $Prods = array();		// persistent buffer with product data and extraShippingCosts index
	var $owner;					// owner of this object
	var $lock = false;			// prevents changes to the cart
	var $taxrates = false;		// hash with rate_name => %
	var $weightrates = false;	// hash with rate->name => array( upper_weight => cost )
	var $xtrShippingType = -1;
	var $taxLocationId = '-1';	// where is the buyer located
	var $coupon = '';			// coupon code

	function ShoppingCart ( $owner ) {

		$this->owner =& $owner;

		// restore from session if present
		if( isset( $_SESSION['CartData'] ) ) {
			$this->Prods = unserialize( $_SESSION['CartData'] );
			if( isset( $_SESSION['Shipping'] ) )
				$this->xtrShippingType = $_SESSION['Shipping'];
			if( isset( $_SESSION['TaxLocation'] ) )
				$this->taxLocationId = $_SESSION['TaxLocation'];
		}
		else
		{
			// initialize
			$this->setTaxLocationId();
			$this->setExtraShippingType();
		}
	}

 /*****************************  CART METHODS  *****************************/

	// find products with the same id AND same options string
	// return true if found and always return a usable key (existing or new )
	function getProdKey ( $id, $options, &$cartid ) {

		$unique = $id;

		if( ! isset($this->Prods[$unique]) ) {
			// this product is not yet in the cart
			$cartid = $unique;
			return false;
		}

		// product is in the cart, check the options
		foreach( $this->Prods as $k => $v ) {

			if( $v['productid'] == $id && $v['options'] == $options ) {
				// this product + options is already in the cart
				$cartid = $k;
				return true;
			} else {
				// extract last used sequence number
				$pos = strpos( $k, SEP );
				$seq = ($pos === false ? 0 : substr($k, $pos + 1) );
			}
		}

		// not found, make new key, use letter for javascript compatability
		$cartid = $unique . SEP . ($seq + 1);
		return false;
	}


	/**
	 *
	 *@param	$options	comma separated list of option indexes
	 *@return cartid on success or '' on failure
	 */
	function addProduct ( &$product, $units, $options ) {

		if( $this->lock ) return '';

		// from a group screen, options may be set to '-1' as undefined,
		// these must be translated to the first default option (if it exists)
		if( $options != '' ) {

			$sel = explode( ',', $options );

			for( $i = 0; $i < count( $sel ); $i++ ) {

		 		if( $sel[$i] == -1 && $product->getOptionItemsByIndex( $i ) ) {
					$sel[$i] = $product->getDefaultOptionValue( $i );
				}
			}

			$options = implode(',', $sel);
		}

		if( $this->getProdKey( $product->productid, $options, $key ) ) {

			// update quantity if product+options is already in basket
			$this->Prods[$key]['qty'] += $units;

		} else {

			$this->Prods[$key] = array(
				'productid' => $product->productid,
				'name' => $product->name,
				'descr' => $product->shortdescription,
				'sku' => $product->sku,
				'price' => $product->yourprice,
				'qty' => $units,
				'taxname' => $product->tax,			// this is the key for the taxrates array
				'handling' => $product->handling,
				'shipping' => $product->shipping,
				'weight' => $product->getWeight(),
				'weightunits' => $product->weightunits,
				'options' => $options );
		}

		return $key;
	}


  	function removeProduct ( &$cartid ) {

		if( $this->lock ) return false;

		$cartid = trim($cartid);

		if( isset($this->Prods[$cartid]) ) {
			unset($this->Prods[$cartid]);
			return true;
		}
		return false;
   	}


  	function emptyCart ( $ignoreLock = false ) {

  		if( $this->lock && !$ignoreLock) return false;

		$this->Prods = array();
		unset( $_SESSION['CartData'] );
		unset( $_SESSION['Shipping'] );
		unset( $_SESSION['CouponCode'] );
  	}


	function saveCart ( ) {
		$_SESSION['CartData'] = serialize( $this->Prods );
		$_SESSION['Shipping'] = $this->xtrShippingType;
		$_SESSION['TaxLocation'] = $this->taxLocationId;
		$_SESSION['CouponCode'] = $this->coupon;
	}


	// export all cart data as a structure that has no dependencies on data.php
	// include the original values so the cart lines can be restore (future function?)
	function exportCart ( ) {
		$export =  array(
			'timestamp' => time(),
			'currency' => $this->owner->getConfigS('shopcurrency'),
			'grandtotal' => $this->getGrandTotalCart(),
			'shipping_handling' => $this->getShippingHandlingTotal(),
			'tax' => $this->getTotalTax(),
			'extrashippingdescr' => $this->owner->getExtraShippingDescr( $this->getExtraShippingIndex() ),
			'lines' => array()
			);

		// remove dependencies on data.php
		foreach( $this->Prods as $cid => $line ) {
			$export['lines'] [ $cid ] = array(
				'productid' => $line['productid'],
				'name' => $line['name'],
				'descr' => $line['descr'],
				'sku' => $line['sku'],
				'price' => $line['price'],
				'qty' => $line['qty'],
				'subtotal' => $this->getSubtotalPriceProduct( $cid ),
				'taxname' => $line['taxname'],								// dependent
				'taxperc' => $this->lookupTaxPerc( $line['taxname'] ),		// independent
				'handling' => $line['handling'],
				'shipping' => $line['shipping'],
				'options' => $line['options'],								// dependent
				'optionstext' => $this->getOptionsAsText ( $cid )			// independent
			 );
		}

		$export['lineitemcount'] = $this->getNumberOfLineItems();

		return $export;
	}


 /*****************************  NAME & DESCR  *****************************/

 	function existsProduct ( $cid ) {
		return isset( $this->Prods[$cid] );
	}


	function getName ( $cartid, $withSKU = false ) {
		if( !isset($this->Prods[$cartid]) )	return '';

		if( $withSKU && isset( $this->Prods[$cartid]['sku'] ) && ! empty( $this->Prods[$cartid]['sku'] ) ) {
			return $this->Prods[$cartid]['name'] . ' ['. $this->Prods[$cartid]['sku'] . ']';
		} else {
			return $this->Prods[$cartid]['name'];
		}
	}


	function getSKU ( $cartid ) {
		if( !isset($this->Prods[$cartid]) || !isset($this->Prods[$cartid]['sku']) )	return '';
		return $this->Prods[$cartid]['sku'];
	}


	function getDescr ( $cartid ) {
		if( !isset($this->Prods[$cartid]) )	return '';
		return $this->Prods[$cartid]['descr'];
	}


	// return product id as in data.php ( != cart id)
	function getId ( $cid ) {
		if( !isset($this->Prods[$cid]) )	return -1;
		return $this->Prods[$cid]['productid'];
	}


 	function setGroupId ( $cid, $groupId ) {
		if( isset($this->Prods[$cid]) ) {
       		$this->Prods[$cid]['groupid'] = $groupId;
  		}
  	}

  	function getProductProperty( $cid, $property ) {

		$prod = $this->owner->getProduct ( $this->getId( $cid ), false);
			
  		if( isset( $prod ) && isset($prod[$property]) )
  			return $prod[ $property ];
  		else
  			return '';
  	}


 /*****************************  OPTIONS  *****************************/


  	// see comment at top of file for $options expected format
 	function setOptionsOfProduct ( &$cartid, $options ) {

		if( $this->lock ) false;

		$cartid = trim($cartid);			// important: template adds spaces!

		if( isset( $this->Prods[$cartid] ) ) {
			$this->Prods[$cartid]['options'] = $options;
			return true;
		} else
			return false;
  	}


	function hasOptions ( $cartid ) {

		$cartid = trim($cartid);			// important: template adds spaces!

		if( !isset($this->Prods[$cartid]) ||
			$this->Prods[$cartid]['options'] == '' ) {
			return false;
		} else {
			return true;
		}
	}


	function getOptions ( $cartid ) {

		$cartid = trim($cartid);			// important: template adds spaces!

		if( !isset($this->Prods[$cartid]) )	return '';

		return $this->Prods[$cartid]['options'];
	}


	function getOptionsAsText ( $cartid, $prefix = '' ) {

		// find the product and it's options
		$prdid = $this->getId( $cartid );
		
		$prod = $this->owner->getProduct ( $prdid, false);
		#echo "Cart ID: $cartid, Product ID: $prdid"; print_r($options);

		if( ! $this->hasOptions( $cartid ) ||
			! $prod->getOptionItemsByIndex( 0 ) )		return '';

		$sel = explode(',', $this->getOptions( $cartid ) );
		$text = '';

		for( $i = 0; $i < count($sel); $i++ ) {

			if( $items = $prod->getOptionItemsByIndex( $i ) ) {
				
				foreach( $items as $item ) {

					if( $item['value'] == $sel[$i] ) {

						$text .= ($text != '' ? ', ' : '') . $item['label'];

						if( isset( $item['price'] ) && $item['price'] != 0.00 ) {
							$text .= '(+'.formatMoney($item['price'], 100).')';
						}
						break;
					}
				}
			}
		}
		return strlen( $text ) == 0 ? '' : $prefix . $text;
	}


	function getPriceOptions( $cartid )
	{
		// find the product and it's options
		$prdid = $this->getId( $cartid );
		
		$prod = $this->owner->getProduct ( $prdid, false);

		#echo "Cart ID: $cartid, Product ID: $prdid"; print_r($options);

		if( ! $this->hasOptions( $cartid ) ||
			! $prod->getOptionItemsByIndex( 0 ) )			return 0.0;

		$sel = explode(',', $this->getOptions( $cartid ) );

		$extra_price = 0.0;

		for( $i = 0; $i < count($sel); $i++ ) {

			if( $items = $prod->getOptionItemsByIndex( $i ) ) {

				foreach( $items as $item ) {

					if( $item['value'] == $sel[$i] ) {

						$extra_price += $item['price'];
						break;
					}
				}
			}
		}

		return $extra_price;
	}

 /*****************************  PRICE & QUANTITY  *****************************/

  	function setUnitsOfProduct ( &$cartid, $units ) {

 		$cartid = trim($cartid);			// important: template adds spaces!

 		if( $this->lock ) false;

		if( isset($this->Prods[$cartid]) ) {
			$this->Prods[$cartid]['qty'] = $units;
			return true;
		} else
			return false;
  	}


	function getUnitsOfProduct ( &$cartid ) {

		$cartid = trim( $cartid );			// important: template adds spaces!

		if( !isset($this->Prods[$cartid]) ||
		 	!isset($this->Prods[$cartid]['qty']) ) return 0;

 		return $this->Prods[$cartid]['qty'];
	}


	function getPrice ( $cid ) {

		if( !isset($this->Prods[$cid]) ) return 0;

		return $this->Prods[$cid]['price'] + $this->getPriceOptions( $cid );
 	}


	// returns price * qty per cart-id
 	function getSubtotalPriceProduct ( $cid, $withDiscount = false ) {

		if( !isset($this->Prods[$cid]) ) return 0;
		#echo "ID: $productid, QTY: {$this->Prods[$cid]['qty']}, Price: {$this->Prods[$cid]['price']}";

		if( $withDiscount && isset( $this->Prods[$cid]['discount'] ) ) {
			return $this->Prods[$cid]['qty'] * $this->getPrice( $cid ) - $this->Prods[$cid]['discount'];
		} else {
			return $this->Prods[$cid]['qty'] * $this->getPrice( $cid );
		}
	}


	// amount with (normal) shipping, handling and tax
	function getTotalPriceProduct ( $cid, $withDiscount = false ) {

		if( !isset($this->Prods[$cid]) )	return 0;

		$price = $this->getSubtotalPriceProduct( $cid, $withDiscount )
			   + ( $this->Prods[$cid]['shipping'] + $this->Prods[$cid]['handling'] ) * $this->getUnitsOfProduct( $cid );

		return $price + $this->getTaxAmountProduct( $cid );
	}


 /*****************************  TAXES  *****************************/

	function setTaxLocationId ( $id = -1 ) {

		if( $id != -1 )	{
			$this->taxLocationId = $id;
			return;
		}

		$this->taxLocationId = $this->owner->getDefaultTaxLocationId();
	}


	function getTaxLocationId ( ) {
		return $this->taxLocationId;
	}


	function setTaxProduct ( $cid, $tax ) {
		if( $this->lock ) return;

		if( isset($this->Prods[$cid]) ) {
			$this->Prods[$cid]['taxname'] = $tax;
		}
 	}


 	// return product tax rate names as an array for the selected location or only true
 	function lookupProductTaxRates ( $returnKeys = false ) {

 		if( ! $this->taxrates )
		{
 			$this->taxrates['Product'] = $this->owner->getConfigS('TaxRates', 'Product');
 			$this->taxrates['Shipping'] = $this->owner->getConfigS('TaxRates', 'Shipping');
		}
			

 		if( isset( $this->taxrates['Product'] ) &&
 			isset( $this->taxrates['Product'][$this->taxLocationId] ) )
 		{

 			return $returnKeys ? array_keys( $this->taxrates['Product'][$this->taxLocationId] ) : true;
 		}
		return false;
 	}


 	// return % in decimal form, i.e. 1% -> 0.01
 	function lookupTaxPerc ( $rate_name ) {

 		if( $this->lookupProductTaxRates() &&
 		    isset( $this->taxrates['Product'][$this->taxLocationId][$rate_name] ) )
 		{
 			$div = pow( 10, $this->owner->getConfigS('TaxRatesDecimals') + 2 );
 			return $this->taxrates['Product'][$this->taxLocationId][$rate_name] / $div ;
 		}
		return 0;
 	}


 	// return % in decimal form, i.e. 1% -> 0.01 (only used for Google Checkout)
 	function lookupTaxPercSpecial ( $subject ) {

 		if( $this->lookupProductTaxRates() &&
 			isset( $this->taxrates[ $subject ] ) &&
 			isset( $this->taxrates[ $subject ][ $this->taxLocationId ] ) )
 		{
 			$div = pow( 10, $this->owner->getConfigS('TaxRatesDecimals') + 2 );
 			return $this->taxrates[$subject][$this->taxLocationId] / $div ;
 		}
		return 0;
 	}


	// return 0.0 if no shipping tax value found
 	function lookupTaxPercShipping ( ) {

 		if( $this->lookupProductTaxRates() &&
 		    isset( $this->taxrates['Shipping'] ) &&
 		    isset( $this->taxrates['Shipping'][$this->taxLocationId] ) )
 		{
			$div = pow( 10, $this->owner->getConfigS( 'TaxRatesDecimals' ) + 2 );
 			return $this->taxrates['Shipping'][$this->taxLocationId] / $div;
 		}

		return 0.0;
 	}


	// return the name of the tax rate for a product
 	function getTaxRateName ( $cid ) {

		if( !isset($this->Prods[$cid]) ) return '';

		return $this->Prods[$cid]['taxname'];
	}


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


 	function hasTaxAmountProduct ( $cid ) {

		if( !isset( $this->Prods[$cid] ) )	return false;

		return $this->lookupTaxPerc( $this->Prods[$cid]['taxname'] ) *  $this->getPrice( $cid ) != 0.0;
	}


	// tax amount for a product * qty rounded to cents
	function getTotalTaxAmountProduct ( $cid ) {

		if( !isset( $this->Prods[$cid] ) )	return 0;

		return $this->getTaxAmountProduct ( $cid ) * $this->getUnitsOfProduct($cid);
	}


	// tax of all products together
 	function getTotalTax ( ) {

 		if( empty( $this->Prods ) ) return 0;

 		$amount = 0;

	 	foreach( $this->Prods as $cid => $value) {
   			$amount += $this->getTotalTaxAmountProduct( $cid );
 		}

		$amount += $this->getTaxAmountExtraShipping ( );

		return $amount;
  	}


 /*****************************  SHIPPING  *****************************/

	function setExtraShippingType ( $index = -1 ) {

		if( $index != -1 )	{

			$previous = $this->xtrShippingType;

			$this->xtrShippingType = $index;

			// verify for weight based shipping that the cart weight is allowed
			if( $this->getShippingHandlingTotal() === false ) {

				 $this->xtrShippingType = $previous;
				 return false;
			}
			return true;
		}

		// set this to the first extrashipping item if it exists
		if( ( $itms = $this->owner->getExtraShippingList() ) ) {

			// Only establish it in case no one will appear on the cart (one or none)
			if( count($itms) == 1 ) {
				$itm = current( $itms );
				$this->xtrShippingType = $itm['id'];
			}
		}

		return true;
	}


	function getExtraShippingIndex ( ) {
		return $this->xtrShippingType;
	}


	// doesn't include extra shipping costs
	function setShippingProduct ( $cid, $shipping ) {

		if( $this->lock ) return;

		if( isset($this->Prods[$cid]) ) {
			$this->Prods[$cid]['shipping'] = $shipping;
		}
 	}


	// doesn't include extra shipping costs
 	function getShippingProduct ( $cid ) {

		if( !isset($this->Prods[$cid]) ||
			!isset($this->Prods[$cid]['shipping']) )
			return 0;

		return $this->Prods[$cid]['shipping'];
 	}


	// doesn't include extra shipping costs
 	function getShippingProducts ( ) {

		$amount = 0;
		$cids = array_keys( $this->Prods );
  		foreach( $cids as $cid ) {
  			$amount += $this->getShippingProduct( $cid ) * $this->getUnitsOfProduct( $cid );
 		}
		return $amount;
 	}


 	//  Minimim_charge is found in the $extrashipping array as the item with type = -1
	function getShippingMinimumCharge ( ) {

		if( isset( $this->owner->extrashipping) &&
			is_array( $this->owner->extrashipping ) )
		{
			foreach( $this->owner->extrashipping as $es ) {
				if( $es['type'] == '-1' ) {
					return $es['amount'];
				}
			}
		}
		return 0;
	}


 	 /* PRIVATE
 	  * Shipping & Handling costs for all products together:
	  *
  	  * If no $method is specified, the actual extra-costs are returned
  	  * else other costs can be calculated, e.g. to pass on to Google Checkout.
  	  */
	function getShippingHandlingTotal_Weight ( $method ) {

		return $amount + $this->getExtraShippingAmount( $method );
 	}

	
 	/* Shipping & Handling costs for all products together:
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
  	 * If no $method is specifies, the actual extra-costs are return
  	 * else other costs can be calculated, e.g. to pass on to Google Checkout.
  	 */
 	function getShippingHandlingTotal ( $method = false ) {

 		if( empty($this->Prods) )
 			return 0;

 		if( $method === false ) {
			$method = $this->getExtraShippingIndex ( );
 		}

		$amount = $this->getShippingProducts() +
				  $this->getHandlingProducts();

 		
		return $amount + $this->getExtraShippingAmount( $method );
 	}


	function getExtraShippingAmount ( $method = false )
	{
		// Returns the amount of extrashipping without taking account of products
		if( $method === false ) {
			$method = $this->getExtraShippingIndex ( );
 		}
		
		if ( $method === false ) {
			return $this->getShippingMinimumCharge();
 		} else {
 			list( $extra_type, $extra_amount ) = $this->owner->getExtraShippingCosts( $method );
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



 /*****************************  HANDLING  *****************************/

	function setHandlingProduct ($cid, $handling ) {

		if( $this->lock ) return;

		if( isset($this->Prods[$cid]) ) {
			$this->Prods[$cid]['handling'] = $handling;
		}
 	}

	// amount for 1 product
 	function getHandlingProduct ( $cid ) {
	
		if( !isset($this->Prods[$cid]) ||
			!isset($this->Prods[$cid]['handling']) ) return 0;
		return $this->Prods[$cid]['handling'];
 	}


	// amount for product * qty * all articles in cart
	function getHandlingProducts ( ) {
		
  		if( empty($this->Prods) )
  			return 0;

  		$amount = 0;
		foreach( $this->Prods as $cid => $value ) {
			$amount += $this->getHandlingProduct( $cid ) * $this->getUnitsOfProduct( $cid );
 		}
		return $amount;
 	}


 /*****************************  COUNTERS & TOTALS  *****************************/

	// # of products = # of articles * quantity of each article
	// if group and product id's are defines:
	// 		# of articles * quantity of each article with groupid and productid
	//		articles with different options are considered the same product!
	function getNumberOfProducts ( $productid = -1) {

		$n = 0;

	 	foreach( $this->Prods as $p ) {

    		if( $productid < 0 || $p['productid'] == $productid )
    		{
	 			$n += $p['qty'];
    		}
  		}
		return $n;
	}


	// products with different options are considered different products
	// set $options to -1 to ignore options
	function getNumberOfOptionProducts ( $productid, $options ) {
		$numProds = 0;
	 	foreach( $this->Prods as $p ) {

    		if( $p['productid'] == $productid  ) {
				// In case we want a specific options product we return the actual quantity
    			if ($p['options'] == $options ) {
					return $p['qty'];
				}
				// Otherwise we try to find all the products in the cart with that ID
				else if ( $options == -1 )
				{
					$numProds += $p['qty'];
				}
			}
  		}
		return $numProds;
	}
	

	// articles corresponds to number of different products (= cart lines)
	function getNumberOfLineItems ( ) {
  		return count( $this->Prods );
 	}


	// calculate the total weight of the cart for all products that have
	// config['shipping_weightunit'] as weight unit.
	function getTotalWeightCart ( ) {

  		if( empty($this->Prods) ) return 0;

  		$weight = 0;
		$wunit = $this->owner->getConfigS( 'shipping_weightunit' );

	 	foreach( $this->Prods as $cid=>$p ) {

	 		if( $p['weightunits'] == $wunit ) {
	   			$weight +=  ( $p['weight'] ? $p['weight'] : 0 ) * $p['qty'];
	 		}
 		}

		return $weight;
	}


	// amount without shipping, handling and tax or discount lines
	function getSubtotalPriceCart ( ) {

		if( empty($this->Prods) ) return 0;
		$amount = 0;

		foreach( $this->Prods as $cid => $p ) {
			if( ($amt = $this->getSubtotalPriceProduct( $cid )) > 0 )
			$amount +=  $amt;
		}

		return $amount;
	}

	// amount without shipping, handling and tax or discount lines
	function getSubTotalDiscountLines ( ) {

		if( empty($this->Prods) ) return 0;
		$amount = 0;

		foreach( $this->Prods as $cid => $p ) {
			if( ($amt = $this->getSubtotalPriceProduct( $cid )) < 0 )
			$amount +=  $amt;
		}

		return $amount;
	}


 	// amount with shipping, handling and tax in cents
   	function getGrandTotalCart ( $withDiscountLines = true ) {

		$amount = $this->getSubtotalPriceCart()
				+ $this->getShippingHandlingTotal()
				+ $this->getTotalTax();

		if( $withDiscountLines )
			$amount += $this->getSubTotalDiscountLines();

		return $amount;
   	}
	
	
 /*****************************  USED BY TEMPLATES  *****************************/

	function getPairProductIdGroupId ( ) {

		if( empty($this->Prods) ) return array();

		$temp= array();
		foreach( $this->Prods as $k => $v ) {
			$temp[] = array( 'cartid' => $k, 'productid' => $v['productid'] );
    	}

		return $temp;
 	}

}

function obf ( $txt, $salt = 13 ) {
	
   $length = strlen( $txt );

   for( $i = 0; $i < $length; $i++ ) {
      $c = 159 - ord( $txt[$i] ) - abs( $salt ) % 94;
      $txt[$i] = chr( $c < 33 ? $c + 94 : $c );
   }

   return $txt;
}

?>
