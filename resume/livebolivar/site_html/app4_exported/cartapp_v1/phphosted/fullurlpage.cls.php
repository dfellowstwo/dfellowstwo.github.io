<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Use for relay response
*  - Any url that is returned by a page::method must have a full url
*  - Filter some other stuff
*  - Inherit from DownloadPage, because we might neeed those methods
*
* @author Cees de Gruijter
* @category SCC Hosted
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

require 'downloadpage.cls.php';


class FullUrlPage extends DownloadPage {

	var $myUrl;

	function FullUrlPage () {

		parent::__construct();

		$this->myUrl = $this->getFullUrl(false, false, true);
		$this->myUrl = substr( $this->myUrl, 0, strrpos( $this->myUrl, '/' ) + 1 );
	}


	function getConfigS( $param1, $param2 = false ) {

		$result = parent::getConfigS( $param1, $param2 );

		switch ( $param1 ) {
			case 'shoplogo':
			case 'viewcarthref':
			case 'home':
			case 'downloadshref':
				if( ! empty( $result ) ) {
					$result = $this->myUrl . $result;
				}
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
