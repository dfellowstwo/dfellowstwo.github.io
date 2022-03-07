<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Compatibility file for Pro version.
*
* @author Cees de Gruijter
* @category SCC Pro
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/

require CARTREVISION . '/php/page.cls.php';

class DownloadPage extends Page {

	function DownloadPage ( )  {
		parent::Page();
	}

	function GetDownloadCode ( &$code ) {
		$code = false;
		return false;
	}

}

?>
