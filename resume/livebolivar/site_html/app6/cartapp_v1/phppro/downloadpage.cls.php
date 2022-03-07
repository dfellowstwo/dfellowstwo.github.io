<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Compatibility file for Pro version.
*
* @author Cees de Gruijter
* @category SCC Pro
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (https://www.coffeecup.com/)
*/

// careful not to overwrite what earlier includes defined
if( empty( $myPageClassName  ) ) $myPageClassName  = 'DownloadPage';

require CARTREVISION . '/phppro/page.cls.php';

class DownloadPage extends ProPage {

	function DownloadPage ( )  {
		parent::Page();
	}

	function GetDownloadCode ( &$code ) {
		$code = false;
		return false;
	}

}

?>
