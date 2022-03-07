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
* @category SCC Hosted
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

// careful not to overwrite what earlier includes defined
global $myPageClassName;
if( empty( $myPageClassName  ) ) $myPageClassName  = 'SearchPage';

// only after setting $myPageClassName it is safe to include other stuff 
require 'phphosted/page.cls.php';


class SearchPage extends HostedPage {

	var $keywords = '';

	function __construct ( )  {
		parent::__construct();

		if( isset( $_GET['keywords'] ) ) {
			$this->keywords = trim ( stripslashes( $_GET['keywords'] ) );
		}

	}


	function getSearchWords ( ) {
		return stripslashes( $this->keywords );
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
			$this->setCartMessage( _T( 'Search function needs words to search for.' ) );
			return array();
		}

		// used cached result or the page may get very slow
		if( $result !== false ) return $result;

		$result = array();

		// split the search terms into an array
		$terms = array();
		$delimiters = " \t,;:/";
		$tok = strtok( $_GET['keywords'], $delimiters );

		while ($tok !== false) {
    		$terms[] = strtolower( trim( $tok) );
    		$tok = strtok( $delimiters );
		}

		// build the query
		$sql = 'SELECT productid, groupid FROM ' . TPRODSEARCH . ' WHERE ' . TPRODSEARCH . 
			   ' MATCH \'';

		$tmp = '';
		foreach( $terms as $term ) {

			// escape ' to prevent sql injection attacks
			$term = str_replace( "'", "\'", stripslashes( $term ) );
			
			$tmp .=  ' ' . $term;			//  implicit 'and' search
		}

		$sql .= substr( $tmp, 1 ) . '\';';

		#die($sql);

		foreach( $this->db->query( $sql, PDO::FETCH_ASSOC ) as $row ) {

			$result[] = array( 'productid' => $row['productid'], 'groupid' => $row['groupid'] );

		}

		return $result;
	}

}

?>