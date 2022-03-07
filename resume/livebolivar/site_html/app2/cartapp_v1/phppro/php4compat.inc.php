<?php
// Define
if (!function_exists('str_split')) {
    function str_split($string, $split_length = 1)
    {
        return php_compat_str_split($string, $split_length);
    }
}

// Define
if (!function_exists('stripos')) {
    function stripos($haystack, $needle, $offset = null)
    {
        return php_compat_stripos($haystack, $needle, $offset);
    }
}




/**
 * Replace stripos()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - https://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        https://php.net/function.stripos
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 1.15 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_stripos($haystack, $needle, $offset = null)
{
    if (!is_scalar($haystack)) {
        user_error('stripos() expects parameter 1 to be string, ' .
            gettype($haystack) . ' given', E_USER_WARNING);
        return false;
    }

    if (!is_scalar($needle)) {
        user_error('stripos() needle is not a string or an integer.', E_USER_WARNING);
        return false;
    }

    if (!is_int($offset) && !is_bool($offset) && !is_null($offset)) {
        user_error('stripos() expects parameter 3 to be long, ' .
            gettype($offset) . ' given', E_USER_WARNING);
        return false;
    }

    // Manipulate the string if there is an offset
    $fix = 0;
    if (!is_null($offset)) {
        if ($offset > 0) {
            $haystack = substr($haystack, $offset, strlen($haystack) - $offset);
            $fix = $offset;
        }
    }

    $segments = explode(strtolower($needle), strtolower($haystack), 2);

    // Check there was a match
    if (count($segments) === 1) {
        return false;
    }

    $position = strlen($segments[0]) + $fix;
    return $position;
}



/**
 * Replace str_split()
 *
 * @category    PHP
 * @package     PHP_Compat
 * @license     LGPL - https://www.gnu.org/licenses/lgpl.html
 * @copyright   2004-2007 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link        https://php.net/function.str_split
 * @author      Aidan Lister <aidan@php.net>
 * @version     $Revision: 269597 $
 * @since       PHP 5
 * @require     PHP 4.0.0 (user_error)
 */
function php_compat_str_split($string, $split_length = 1)
{
    if (!is_scalar($split_length)) {
        user_error('str_split() expects parameter 2 to be long, ' .
            gettype($split_length) . ' given', E_USER_WARNING);
        return false;
    }

    $split_length = (int) $split_length;
    if ($split_length < 1) {
        user_error('str_split() The length of each segment must be greater than zero', E_USER_WARNING);
        return false;
    }
    
    // Select split method
    if ($split_length < 65536) {
        // Faster, but only works for less than 2^16
        preg_match_all('/.{1,' . $split_length . '}/s', $string, $matches);
        return $matches[0];
    } else {
        // Required due to preg limitations
        $arr = array();
        $idx = 0;
        $pos = 0;
        $len = strlen($string);

        while ($len > 0) {
            $blk = ($len < $split_length) ? $len : $split_length;
            $arr[$idx++] = substr($string, $pos, $blk);
            $pos += $blk;
            $len -= $blk;
        }

        return $arr;
    }
}


?>
