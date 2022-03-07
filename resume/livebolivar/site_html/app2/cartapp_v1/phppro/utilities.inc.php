<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Stuff that comes in handy and/or is common to all pages.
*
* @version $Revision: 2930 $
* @author Cees de Gruijter
* @category SCC PRO
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (https://www.coffeecup.com/)
*/

// include the base version of this file too
require_once CARTREVISION . '/php/utilities.inc.php';

if( ! function_exists( 'getFileLock' ) ) {
	function getFileLock ( &$handle, $lockType ) {

		$retries = 0;
	    $max_retries = 100;

	    do {
	        if ($retries > 0) {
	            usleep( rand(1, 1000) );
	        }
	        $retries += 1;
	    } while( ! flock( $handle, $lockType) && $retries <= $max_retries );

	    if( $retries == $max_retries )
	    	return false;
	    else
	    	return true;
	}

	// load PHP4 compatibility functions (from the PEAR::PHP_Compat package) if needed
	if( strcmp( PHP_VERSION, '5.0' ) < 0 ) {
		include 'php4compat.inc.php';
	}
}

function RandomString ( $length ) {
	$chrs = '1234567890abcdefghijklmnopqrstuvwxyz';
	$result = '';
	while( $length-- ) {
		$result .= $chrs[ rand ( 0 , 35) ];
	}
	return $result;
}


// expected format of users: array( user1 => password1, ... )
function Authenticate ( $users, $realm ) {

	// apache expects that realm has some value
	if( empty( $realm ) ) $realm = 'CoffeeCup Shopping Cart Software';

	// HTTP Auth is only available when PHP runs as Apache module - use session alternative
	if( strpos( strtoupper(PHP_OS), 'WIN' ) === 0 ||
		strpos( strtolower(PHP_SAPI), 'cgi' ) !== false ) {

		return AuthSession( $users, $realm );

 	} else if( strcmp( phpversion(), '5.1') >= 0 ) {

		// PHP_AUTH_DIGEST was added in PHP 5.1 - switch to Basic if needed
 		return AuthDigest( $users, $realm );

 	} else {

 		return AuthBasic( $users, $realm );

 	}
}


function AuthDigest( $users, $realm ) {

	if( empty( $_SERVER['PHP_AUTH_DIGEST'] ) ||
		! ( $data = http_digest_parse( $_SERVER['PHP_AUTH_DIGEST'] ) ) ||
		! isset( $users[ $data['username'] ] ) )
	{
		if( isset( $data['username'] ) ) {
			writeErrorLog ( 'Failed authentication for userid: "' . $data['username'] . '".' );
		}
		DenyAccess( $realm );
	}

	// generate the valid response
	$A1 = md5( $data['username'] . ':' . $data['realm'] . ':' . $users[ $data['username'] ] );
	$A2 = md5( $_SERVER['REQUEST_METHOD'].':'.$data['uri'] );
	$valid_response = md5( $A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' .
						   $data['cnonce'] . ':' . $data['qop'].':'.$A2 );

	if( $data['response'] != $valid_response || trim($realm) != trim($data['realm']) ) {

		writeErrorLog ( 'Failed authentication for userid "' . $data['username'] . '" in realm "' . $data['realm'] . '".' );
		DenyAccess( $realm );

	}

	return $data['username'];
}


function AuthBasic ( $users, $realm ) {

	if( ! isset( $_SERVER['PHP_AUTH_USER'] ) ||
		! isset( $users[ $_SERVER['PHP_AUTH_USER'] ] ) ||
		strcmp( $users[ $_SERVER['PHP_AUTH_USER'] ], $_SERVER['PHP_AUTH_PW'] ) != 0 )
	{
		DenyAccess( $realm );
	}

	return  $_SERVER['PHP_AUTH_USER'];
}


function AuthSession ( $users, $realm ) {

	$mypath = substr( $_SERVER['PHP_SELF'], 0, strrpos( $_SERVER['PHP_SELF'] , '/' ) );
	
	if( isset( $_SESSION['access'] ) &&
		$_SESSION['access'] = md5('TLE64Lmi pGUCQkz4nNgfbw8OFF1H0tKv6a941HFO' . $_SESSION['user'] . $realm . $mypath  ) )
	{
		return	$_SESSION['user'] ;
	}

	if( ! isset( $_POST['field1'] ) ||
		! isset( $_POST['field2'] ) ||
		empty( $_POST['field1'] ) ||
		empty( $_POST['field2'] ) ||
		strcmp( $users[ $_POST['field1'] ], $_POST['field2'] ) != 0 )
	{
		// user is asking for a session based login
		global $myPage;
		include 'login.inc.php';
		exit();

	} else {
		
		// mix-in the realm and path so that switching from one shop to another on the same session is prevented
		$_SESSION['access'] = md5('TLE64Lmi pGUCQkz4nNgfbw8OFF1H0tKv6a941HFO' . $_POST['field1'] . $realm . $mypath );
		$_SESSION['user'] = $_POST['field1'];
		unset( $_SESSION['sessionended'] );
	}

	return $_POST['field1'];
}


function DenyAccess ( $realm ) {

	if( isset( $_SESSION['user'] ) ) unset( $_SESSION['user'] );
	if( isset( $_SESSION['access'] ) || isset( $_SESSION['sessionended'] ) ) {

		unset( $_SESSION['access'] );
		$_SESSION['sessionended'] = 1;			// marker to prevent sending http-auth headers
		// user is asking for a session based login
		global $myPage;
		include 'login.inc.php';
		exit(0);
	}

	header('HTTP/1.1 401 Unauthorized');

 	if( strcmp( phpversion(), '5.1') >= 0  && strpos( strtolower(PHP_SAPI), 'cgi' ) === false ) {
		header('WWW-Authenticate: Digest realm="' . $realm . '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5( $realm ) . '"');
 	} else {
 	   header('WWW-Authenticate: Basic realm="' . $realm . '"' );
 	}

 	exit(0);
}


// function to parse the http auth header
function http_digest_parse ( $txt )
{
    // protect against missing data
    $needed_parts = array( 'nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1 );

    $data = array();
    preg_match_all( '@(\w+)=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER );

    foreach( $matches as $m ) {
        $data[ $m[1] ] = $m[3] ? $m[3] : $m[4];
        unset( $needed_parts[ $m[1] ] );
    }

    return empty( $needed_parts ) ? $data: false;
}


?>
