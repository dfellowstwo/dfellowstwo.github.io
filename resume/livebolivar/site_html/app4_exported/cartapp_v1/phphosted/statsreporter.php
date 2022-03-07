<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Connect to statalytics
*
* @author Cees de Gruijter
* @category SCC S-DRIVE
* @copyright Copyright (c) 2011 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

class StatsReporter {
	
	var $base_fields;
	var $page;
	var $curl;
	var $base_url;
	
	function __construct ( $page ) {

		$this->page = $page;
		
		$this->base_fields = 'sdrive_account_id=' . $page->sdrive['sdrive_account_id']
					  	   . '&session_id=' . session_id()
					  	   . '&http_referrer=' . urlencode( $_SERVER['HTTP_REFERER'] )
					  	   . '&ip=' . $_SERVER['REMOTE_ADDR'];
		
		// remove trailing '/' if present			
		$this->base_url = preg_replace( '/\/$/', '', $page->sdrive['sdrive_account_statalytics_url'] ); 

		$this->curl = curl_init();
		curl_setopt( $this->curl, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $this->curl, CURLOPT_POST, 1 );
		curl_setopt( $this->curl, CURLOPT_FAILONERROR, 1 );

		// turnoff the server and peer verification
		curl_setopt( $this->curl, CURLOPT_SSL_VERIFYPEER, FALSE );
		curl_setopt( $this->curl, CURLOPT_SSL_VERIFYHOST, FALSE );		
	}

	function __destruct ( ) {
		
		curl_close( $this->curl );
	}


	function NotifyProdView ( $productid ) {
		
		$p = $this->page->getProduct( $productid, false );

		$fields = '&item_sku=' . $p['sku']
				. '&item_name=' . urlencode( $p['name'] );
				
		$this->_do_call( '/product/view', $fields );
	}


	function NotifyOtherPageView ( $pagename ) {

		$fields = '&page=' . urlencode( $pagename );
		
		$this->_do_call( '/page/view', $fields );
	}


	function NotifySearchView ( $searchstring ) {

		$fields = '&search=' . urlencode( $searchstring );
		
		$this->_do_call( '/search/view', $fields );
	}


	function NotifyCategoryView ( $categoryname ) {

		$fields = '&category_name=' . urlencode( $categoryname );
		
		$this->_do_call( '/category/view', $fields );
	}


	function NotifyCartAdd ( $sku, $name, $qty ) {
		
		$p = $this->page->getProduct( $productid, false );

		$fields = '&item_sku=' . $sku
				. '&item_name=' . urlencode( $name )
				. '&quantity=' . $qty ;

		$this->_do_call( '/cart/add', $fields );
	}


	function NotifyCartRemoved ( $sku, $name ) {
		
		$fields = '&item_sku=' . $sku
				. '&item_name=' . urlencode( $name );
		
		$this->_do_call( '/cart/remove', $fields );
	}


	function NotifyCartView ( ) {
				
		$this->_do_call( '/cart/view', '' );
	}


	function NotifyCheckoutAttempt ( $gateway ) {
		
		// json encode the cart contents
		$cart = array();
		foreach( $this->page->cart->Prods as $cid => $p ) {
			$cart[] = array( 'sku' => $p['sku'],
							 'options' => $p['options'],
							 'quantity' => $p['qty'] );
 		}
		
		$fields = '&cart_contents=' . json_encode( $cart )
				. '&selected_gateway=' . urlencode( $gateway );

		$this->_do_call( '/cart/checkout', $fields );
	}
	
	
	function NotifyCheckoutPayment ( $productid ) {
		
		$this->_do_call( '/cart/payment', $fields );
	}
	
	
	
	/************************** private methods *******************************/
	
	function _do_call ( $path, $fields ) {

		curl_setopt( $this->curl, CURLOPT_URL, $this->base_url . $path );
		curl_setopt( $this->curl, CURLOPT_POSTFIELDS, $this->base_fields . $fields );
		curl_setopt( $this->curl, CURLOPT_CONNECTTIMEOUT, 1 );		// ensure the page won't wait too long

		$response = curl_exec( $this->curl );

		if( curl_errno( $this->curl ) ) {
			$effurl = curl_getinfo( $this->curl, CURLINFO_EFFECTIVE_URL );
			writeErrorLog( 'Stats Reporter error - ' . curl_errno( $this->curl )  . ': ' . curl_error( $this->curl ), $effurl . '   ' . $this->base_fields . $fields );
			return false;
		}
		#echo $this->base_fields;
		#writeErrorLog( 'Stats Reporter success - ' . $response, $fields );

		return true;
	}

}


?>