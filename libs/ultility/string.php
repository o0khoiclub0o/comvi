<?php
/**
 * String handling class for utf-8 data
 * Wraps the phputf8 library
 * All functions assume the validity of utf-8 strings.
 *
 * @static
 * @package		Comvi.Framework
 * @subpackage	Utilities
 */
abstract class CString
{
	/**
	 * Provides a secure hash based on a seed
	 *
	 * @param	string	$seed	Seed string.
	 * @return	string
	 */
	public static function getHash($seed, $salt = null)
	{
		if ($salt == null) {
			$salt = CLoader::getConfig()->get('secret');
		}

		return md5($seed.$salt);
	}

	public static function generateRandomString($length = 5) {
		$chars			= '0123456789abcdefghijklmnopqrstuvwxyz';
		$chars_length	= strlen($chars)-1;
		$result			= '';

		for ($i = 0; $i < $length; $i++) {
			$result .= $chars[mt_rand(0, $chars_length)];
		}

		return $result;
	}

	public static function generateSalt() {
		return self::generateRandomString(5);
	}

	/**
	 * Does a UTF-8 safe version of PHP parse_url function
	 * @see http://us3.php.net/manual/en/function.parse-url.php
	 * 
	 * @param string URL to parse
	 * @return associative array or false if badly formed URL. 
	 */	
	public static function parseUrl($url) {
		$result = array();
		// Build arrays of values we need to decode before parsing
		$entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
		$replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "$", ",", "/", "?", "%", "#", "[", "]");
		// Create encoded URL with special URL characters decoded so it can be parsed
		// All other characters will be encoded
		$encodedURL = str_replace($entities, $replacements, urlencode($url));
		// Parse the encoded URL
		$encodedParts = parse_url($encodedURL);
		// Now, decode each value of the resulting array
		foreach ($encodedParts as $key => $value) {
			$result[$key] = urldecode($value);
		}
		return $result;
	}
}
