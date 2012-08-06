<?php
if (!defined('MAGIC_QUOTES_GPC'))	{
	define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	unset($GLOBALS, $_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);
}

/**
 * CInput Class
 *
 * @static
 * @package		Comvi.Framework
 * @subpackage	Environment
 */
class CInput
{
	public $ip;
	public $user_agent;

	public $request;
	public $get;
	public $post;
	public $cookie;
	//public $server;
	public $files;

	public $uri;

	public $state;


	/**
	 * Class constructor.
	 */
	public function __construct()
	{
		//$this->ip			= $this->getIP();
		$this->user_agent	= $_SERVER['HTTP_USER_AGENT'];

		$this->request	= &self::addslashes($_REQUEST);
		$this->get		= &self::addslashes($_GET);
		$this->post		= &self::addslashes($_POST);
		$this->cookie	= &self::addslashes($_COOKIE);
		//$this->server	= &self::addslashes($_SERVER);
		$this->files	= &self::addslashes($_FILES);

		$this->uri		= $this->getURI();
	}

	/**
	 * Returns the global CURI object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param	string $uri The URI to parse.  [optional: if null uses script URI]
	 * @return	CURI  The URI object.
	 */
	public function getURI()
	{
		// Determine if the request was over SSL (HTTPS).
		if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) {
			$https = 's://';
		}
		else {
			$https = '://';
		}

		/*
		 * Since we are assigning the URI from the server variables, we first need
		 * to determine if we are running on apache or IIS.  If PHP_SELF and REQUEST_URI
		 * are present, we will assume we are running on apache.
		 */
		if (!empty($_SERVER['PHP_SELF']) && !empty($_SERVER['REQUEST_URI']))
		{
			// To build the entire URI we need to prepend the protocol, and the http host
			// to the URI string.
			$theURI = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

			// Since we do not have REQUEST_URI to work with, we will assume we are
			// running on IIS and will therefore need to work some magic with the SCRIPT_NAME and
			// QUERY_STRING environment variables.
			
			if (strlen($_SERVER['QUERY_STRING']) && strpos($_SERVER['REQUEST_URI'], $_SERVER['QUERY_STRING']) === false) {
				$theURI .= '?'.$_SERVER['QUERY_STRING'];
			}
		}
		else
		{
			// IIS uses the SCRIPT_NAME variable instead of a REQUEST_URI variable... thanks, MS
			$theURI = 'http' . $https . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];

			// If the query string exists append it to the URI string
			if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
				$theURI .= '?' . $_SERVER['QUERY_STRING'];
			}
		}

		CLoader::import('environment.uri', 1);
		return new CURI($theURI);
	}

	public function getIP()
	{
		if (!isset($this->ip)) {
			// check ip from share internet
			if (isset($_SERVER['HTTP_CLIENT_IP'])) {
			  $this->ip = $_SERVER['HTTP_CLIENT_IP'];
			}
			//to check ip is pass from proxy
			elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			  $this->ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			else {
			  $this->ip = $_SERVER['REMOTE_ADDR'];
			}
		}

		return $this->ip;
	}

	public static function &addslashes(&$string, $force = false, $strip = false)
	{
		if(!MAGIC_QUOTES_GPC || $force == true) {
			if(is_array($string)) {
				foreach($string as $key => $val) {
					$string[$key] = self::addslashes($val, $force, $strip);
				}
			} else {
				$string = addslashes($strip ? stripslashes($string) : $string);
			}
		}
	
		return $string;
	}

	/**
	 * Fetches and returns a given variable.
	 *
	 * The default behaviour is fetching variables depending on the
	 * current request method: GET and HEAD will result in returning
	 * an entry from $_GET, POST and PUT will result in returning an
	 * entry from $_POST.
	 *
	 * You can force the source by setting the $method parameter:
	 *
	 *	post	$_POST
	 *	get		$_GET
	 *	files	$_FILES
	 *	cookie	$_COOKIE
	 *	env		$_ENV
	 *	server	$_SERVER
	 *	method	via current $_SERVER['REQUEST_METHOD']
	 *	default	$_REQUEST
	 *
	 * @param	string	$name		Variable name.
	 * @param	string	$default	Default value if the variable does not exist.
	 * @param	string	$method		Where the var should come from (POST, GET, FILES, COOKIE, METHOD).
	 * @param	string	$type		Return type for the variable, for valid values see {@link CFilterInput::clean()}.
	 * @param	int		$mask		Filter mask for the variable.
	 * @return	mixed	Requested variable.
	 */
	public function get($name, $default = null, $method = 'request', $type = 'none', $mask = 3)
	{
		if ($method === 'method') {
			$method = strtolower($_SERVER['REQUEST_METHOD']);
		}

		$input =& $this->$method;

		if (isset($input[$name]) && $input[$name] !== null) {
			// Get the variable from the input hash and clean it
			$var = self::_cleanVar($input[$name], $mask, $type);
		}
		elseif ($default !== null) {
			// Clean the default value
			$var = self::_cleanVar($default, $mask, $type);
		}
		else {
			$var = null;
		}

		return $var;
	}

	public function getInt($name, $default = 0, $method = 'method')
	{
		return $this->get($name, $default, $method, 'int');
	}

	public function getFloat($name, $default = 0.0, $method = 'method')
	{
		return $this->get($name, $default, $method, 'float');
	}

	public function getBool($name, $default = false, $method = 'method')
	{
		return $this->get($name, $default, $method, 'bool');
	}

	public function getWord($name, $default = '', $method = 'method')
	{
		return $this->get($name, $default, $method, 'word');
	}

	public function getCmd($name, $default = '', $method = 'method')
	{
		return $this->get($name, $default, $method, 'cmd');
	}

	public function getString($name, $default = '', $method = 'method', $mask = 0)
	{
		return $this->get($name, $default, $method, 'string', $mask);
	}

	
	public function getState($name, $default = null)
	{
		return $this->get($name, $default, 'state');
	}

	/**
	 * Clean up an input variable.
	 *
	 * @param mixed The input variable.
	 * @param int Filter bit mask.
	 * 1=trim
	 * 2=allow_html: HTML is allowed, but passed through a safe
	 * HTML filter first. If set, no more filtering is performed. If no bits
	 * other than the 1 bit is set, a strict filter is applied.
	 * @param string The variable type {@see CFilterInput::clean()}.
	 */
	static function _cleanVar($var, $mask = 3, $type = null)
	{
		if (($mask & 1) && is_string($var)) {
			$var = trim($var);
		}

		if ($mask & 2)
		{
			$var = self::cleanVarType($var, $type);
		}

		return $var;
	}

	/**
	 * Method to be called by another php script. Processes for XSS and
	 * specified bad code.
	 *
	 * @param	mixed	$source	Input string/array-of-string to be 'cleaned'
	 * @param	string	$type	Return type for the variable (INT, FLOAT, BOOLEAN, WORD, ALNUM, CMD, BASE64, STRING, ARRAY, PATH, NONE)
	 * @return	mixed	'Cleaned' version of input parameter
	 */
	public static function cleanVarType($source, $type = 'string')
	{
		// Handle the type constraint
		switch (strtoupper($type))
		{
			case 'BOOL' :
			case 'BOOLEAN' :
				$result = (bool) $source;
				break;

			case 'INT' :
			case 'INTEGER' :
				// Only use the first integer value
				preg_match('/-?[0-9]+/', (string) $source, $matches);
				$result = (int) $matches[0];
				break;

			case 'FLOAT' :
			case 'DOUBLE' :
				// Only use the first floating point value
				preg_match('/-?[0-9]+(\.[0-9]+)?/', (string) $source, $matches);
				$result = (float) $matches[0];
				break;

			case 'WORD' :
				$result = (string) preg_replace('/[^A-Z_]/i', '', $source);
				break;

			case 'ALNUM' :
				$result = (string) preg_replace('/[^A-Z0-9]/i', '', $source);
				break;

			case 'CMD' :
				$result = (string) preg_replace('/[^A-Z0-9_\.-]/i', '', $source);
				$result = ltrim($result, '.');
				break;

			case 'BASE64' :
				$result = (string) preg_replace('/[^A-Z0-9\/+=]/i', '', $source);
				break;

			case 'STRING' :
				//$result = (string) $this->_remove($this->_decode((string) $source));
				$result = (string) $source;
				break;

			case 'HTML' :
				//$result = (string) $this->_remove((string) $source);
				$result = (string) $source;
				break;

			case 'ARRAY' :
				$result = (array) $source;
				break;

			case 'PATH' :
				$pattern = '/^[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*([\\\\\/][A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/';
				preg_match($pattern, (string) $source, $matches);
				$result = @ (string) $matches[0];
				break;

			case 'USERNAME' :
				$result = (string) preg_replace('/[\x00-\x1F\x7F<>"\'%&]/', '', $source);
				break;

			default :
				$result = $source;
				break;
		}

		return $result;
	}

	/**
	 * Set a variabe in on of the request variables.
	 *
	 * @param	string	$name		Name
	 * @param	string	$value		Value
	 * @param	string	$method		Hash
	 * @param	boolean	$overwrite	Boolean
	 * @return	boolean
	 */
	public function set($name, $value = null, $method = 'method', $overwrite = true)
	{
		if ($method === 'method') {
			$method = strtolower($_SERVER['REQUEST_METHOD']);
		}

		$input =& $this->$method;

		//If overwrite is false, makes sure the variable hasn't been set yet
		if (!$overwrite && array_key_exists($name, $input)) {
			return false;
		}

		//$previous = array_key_exists($name, $input) ? $input[$name] : null;

		$input[$name] = $value;

		switch ($method) {
			case 'get' :
			case 'post' :
			case 'cookie' :
				$this->request[$name] = $value;
				break;
		}

		return true;
	}

	/**
	 * Sets a request variable.
	 *
	 * @param	array	An associative array of key-value pairs.
	 * @param	string	The request variable to set (POST, GET, FILES, METHOD).
	 * @param	boolean	If true and an existing key is found, the value is overwritten, otherwise it is ignored.
	 */
	public function setVars($array, $hash = 'default', $overwrite = true)
	{
		foreach ($array as $key => $value) {
			$this->set($key, $value, $hash, $overwrite);
		}
	}

	public function setState($name, $value = null)
	{
		return $this->set($name, $value, 'state');
	}
}
?>