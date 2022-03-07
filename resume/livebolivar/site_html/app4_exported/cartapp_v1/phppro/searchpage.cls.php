<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Extension of Page for checking out with Auth.Net.
*
*
* The auth.net form does not show any shipping/handling info on the credit card form (and there
* is no way to add it).
* Solution: abuse the description field for this pupose.
*
* @version $Revision: 1997 $
* @author Cees de Gruijter
* @category SCC
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

// careful not to overwrite what earlier includes defined
global $myPageClassName;
if( empty( $myPageClassName  ) ) $myPageClassName  = 'SearchPage';

// only after setting $myPageClassName it is safe to include other stuff 
require CARTREVISION . '/phppro/page.cls.php';

class SearchPage extends ProPage {

	var $keywords = '';

	function SearchPage ( ) {

		parent::ProPage();

		if( isset( $_GET['keywords'] ) )
			$this->keywords = trim ($_GET['keywords'] );
	}


	function getSearchWords ( ) {
		return $this->keywords;
	}


	// return info in same format as group array
	function getGroup ( $dummy ) {

		if( $this->keywords == '' )
			return array();

		return array(	'name' => _T('Search results for: "') . $this->keywords . '"',		// name / header
    					'metakeywords' => $this->keywords,									// search words
    					'metadescription' => '',											// not used
    					'groupid' => '-1',													// dummy id
    					'pagehref' => $this->getFullUrl(),									// myself
						'productsIds' => array() );											// not used
	}


	// return info about the search in the same format as e.g. starred_products
	function getProductsByGroup ( $dummy ) {

		static $result = false;

		if( $this->keywords == '' ) {
			$this->message = _T( "Search function needs words to search for." );
			return array();
		}

		// used cached result or the page may get very slow
		if( $result !== false ) return $result;

		$result = array();

		// split the search terms into an array and do a 'and' search
		$terms = array();
		$delimiters = " \t,;:/";
		$tok = strtok( $_GET['keywords'], $delimiters );

		while ($tok !== false) {
    		$terms[] = trim( $tok);
    		$tok = strtok( $delimiters );
		}

		// loop through all products and add those that contain the search words
		foreach( $this->products as $prd ) {

			$match = 0;

			foreach( $terms as $term ) {

				if( stripos( $prd['name'], $term ) !== false ||
					stripos( $prd['shortdescription'], $term ) !== false ||
					stripos( $prd['longdescription'], $term ) !== false ||
					stripos( $prd['metakeywords'], $term ) !== false )
				{
					 $match += 1;
				}

			}

			if( $match == count( $terms ) ) {
				$result[] = array( 'productid' => $prd['productid'], 'groupid' => $prd['groupid'] );
			}

		}

		return $result;
	}

}

?>