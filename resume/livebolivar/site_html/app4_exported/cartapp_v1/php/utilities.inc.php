<?php
/**
* CoffeeCup Software's Shopping Cart Creator.
*
* Stuff that comes in handy and/or is common to all pages.
*
* @version $Revision: 2930 $
* @author Cees de Gruijter
* @category SCC PRO
* @copyright Copyright (c) 2009 CoffeeCup Software, Inc. (http://www.coffeecup.com/)
*/


// write to error log in store folder
// $text1 or 2 are flattened if they are simple arrays.
// $text1 and $text2 are concatenated with a space
if( ! function_exists( 'writeErrorLog' ) ) {
	function writeErrorLog ( $text1, $text2 = false ) {

		global $absPath;
		global $errorLoggingType;
		
		$log = '';
		$prefix = '';
		$postfix = '';
		
		if( $errorLoggingType == 3 ) {

			if( ! file_exists( $absPath . 'ccdata/store' ) ) {

				// don't log if the target folder doesn't exist
				return;	

			} else {
				
				// create empty log with some access protection if it doesn't exist yet 
				$log = $absPath . 'ccdata/store/cart_error.log.php';
				if( ! file_exists( $log ) )		@error_log( "<?php echo 'Access denied.'; exit(); ?>\n", 3, $log );
				
				// in a file, we need to add a timestamp and a new line
				$prefix = date( 'r');
				$postfix = "\n";
			}
		} else {
			
			// in the hosted environment, we should add a userid to the log
			global $sdrive_config;
			$prefix = 'sdrive_account=' . $sdrive_config['sdrive_account_id'];
		}

		if( empty( $text1 ) ) $text1 = 'Error logger was called with empty text.';

		$text = '';
		foreach( func_get_args() as $arg ) {

			$text .= ' ';

			if( is_array( $arg ) ) {

				foreach( $arg as $key => $value ) {

					if( is_array( $value ) ) $value = implode( ',', $value );

					$text .= '[' . $key . '] ' . $value . '   ';
				}

			} else {

				$text .= $arg;
			}
		}

		// if it fails, it should fail silently
		@error_log( $prefix . ': ' . trim( $text ) . $postfix, $errorLoggingType, $log );
	}
}  // end function_exists


// GetText-like translator
if( ! function_exists( '_T' ) ) {
	function _T( $text, $vars = false ) {

		global $myPage;
		static $lang = false;

		// load language table if necessary
		if( ! $lang ) {
			$file = getLangIncludePath( 'language.dat.php' );
			if( file_exists( $file ) ) {
				$handle = fopen( $file, "r");
				$sdat = fread( $handle, filesize( $file ) );
				fclose( $handle );
				$lang = unserialize( $sdat );
			}
		}

		if( ! empty( $lang ) && isset($lang[$text]) ) {
			$translated = $lang[$text];
		} else {
			$translated =  $text;
		}

		// replace %s markers with values in vars
		if( $vars ) {

			foreach( $vars as $var ) {

				$pos = strpos( $translated, '%s' );

				if( $pos !== false ) {
					$translated = substr( $translated, 0, $pos )
								. $var
								. substr( $translated, $pos + 2 );
				}
			}
		}

		return $translated;
	}
}  // end function_exists


// return path to file in correct language or safe fallback if file does not (yet) exist
if( ! function_exists( 'getLangIncludePath' ) ) {
	function getLangIncludePath ( $filename ) {

		global $myPage;
		
		if( empty( $filename ) ) return false;

		$filename = ltrim( $filename, '/ ');
		$path = false;
		$lng = $myPage->getConfigS( 'lang' );

		if( $lng && $lng != 'en' ) {
			$path = $lng . '/' . $filename;
		}

		// use fopen to check file existence in include path
		if( $path !== false ) {

			$handle = @fopen( $path, 'r', 1 );

			if( $handle ) {
				fclose( $handle );
				return $path;
			}
		}

		return $filename ;
	}
}


// include server info
function getFullUrl ( $altPage = false, $includeQuery = true, $urlEncode = false ) {

	// some servers use redirection which causes SERVER_NAME != 'url_we_need'
	// test on _SERVER["REDIRECT_SCRIPT_URI"] if this may be the case
	if( isset( $_SERVER['HTTP_HOST'] ) )
		$servername = $_SERVER['HTTP_HOST'];
	else
		$servername = $_SERVER['SERVER_NAME'];

	if( $urlEncode ) {
		// encode the folders, not the '/'!
		$tmp = explode( '/',  trim( $this->getUrl(), '/') );
		for( $i = 0; $i < count( $tmp ); ++$i ) {
			$tmp[$i] = rawurlencode( $tmp[$i] );
		}
		$script = '/' . implode( '/', $tmp );
	} else {
		$script = getUrl();
	}

	if( $_SERVER['QUERY_STRING'] && $includeQuery )
		$script .= '?' . htmlspecialchars($_SERVER['QUERY_STRING'], ENT_NOQUOTES, 'UTF-8');

	// windows servers may set [HTTPS] => off, linux server usually don't set [HTTPS] at all
	if( isset( $_SERVER['HTTPS'] ) && ! empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] != 'off' ) {
		    $protocol = 'https';
	} else {
		    $protocol = 'http';
	}

	$url = $protocol . '://' . $servername;

	// only add the serverport when it differs from the default
	if( strpos( $servername, ':') === false &&
		( $_SERVER['SERVER_PORT'] != '80' || $protocol != 'http') ) {
		$url .= ':' . $_SERVER['SERVER_PORT'];
	}

	$url .= $script;

	if( $altPage )
		$url = substr($url, 0, strrpos($url, '/') + 1 ) . $altPage;

	return $url;
}


?>
