<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
*
*
* @author Cees de Gruijter
* @category SCC PRO
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

require CARTREVISION . '/phppro/page.cls.php';

// Use for relay response
// any url that is returned by a page::method must have a full url
// and filter some other stuff
// extend the ProPage class because we need some methods for stock control
class FullUrlPage extends ProPage {

	var $myUrl;

	function FullUrlPage () {

		parent::Page();

		$this->myUrl = $this->getFullUrl(false, false, true);
		$this->myUrl = substr( $this->myUrl, 0, strrpos( $this->myUrl, '/' ) + 1 );
	}


	function getConfigS( $param1, $param2 = false ) {

		$result = parent::getConfigS( $param1, $param2 );

		switch ( $param1 ) {
			case 'shoplogo':
			case 'viewcarthref':
			case 'home':
				if( ! empty( $result ) )
					$result = $this->myUrl . $result;
		}
		return $result;
	}


	function getUrl(  $query = false ) {
		return $this->myUrl . parent::getUrl( $query );
	}


	function getGroups() {

		$myGroups = parent::getGroups();

		for( $i = 0; $i < count($myGroups); ++$i ) {
			$myGroups[$i]['pagehref'] = $this->myUrl . $myGroups[$i]['pagehref'];
		}
		return $myGroups;
	}

}

?>
