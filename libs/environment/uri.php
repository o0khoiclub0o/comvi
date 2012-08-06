<?php
/**
 * CURI Class
 *
 * This class serves two purposes. First to parse a URI and provide a common interface
 * for the Comvi Framework to access and manipulate a URI.  Second to attain the URI of
 * the current executing script from the server regardless of server.
 *
 * @package		Comvi.Framework
 * @subpackage	Environment
 */
class CURI
{
	/**
	 * @var string Original URI
	 */
	protected $_uri = null;

	/**
	 * @var string Protocol
	 */
	protected $_scheme = null;

	/**
	 * @var string Host
	 */
	protected $_host = null;

	/**
	 * @var integer Port
	 */
	protected $_port = null;

	/**
	 * @var string Username
	 */
	protected $_user = null;

	/**
	 * @var string Password
	 */
	protected $_pass = null;

	/**
	 * @var string Path
	 */
	protected $_path = null;

	/**
	 * @var string Query
	 */
	protected $_query = null;

	/**
	 * @var string Anchor (everything after the "#")
	 */
	protected $_fragment = null;

	/**
	 * @var array Query variable hash.
	 */
	protected $_vars = array();


	/**
	 * Constructor.
	 * You can pass a URI string to the constructor to initialise a specific URI.
	 *
	 * @param	string $uri The optional URI string
	 */
	public function __construct($uri = null)
	{
		if (!is_null($uri)) {
			$this->parse($uri);
		}
	}

	/**
	 * Parse a given URI and populate the class fields.
	 *
	 * @param	string $uri The URI string to parse.
	 * @return	boolean True on success.
	 */
	public function parse($uri)
	{
		// Initialise variables
		$retval = false;

		// Set the original URI to fall back on
		$this->_uri = $uri;

		/*
		 * Parse the URI and populate the object fields.  If URI is parsed properly,
		 * set method return value to true.
		 */
		CLoader::import('ultility.string', 1);
		if ($_parts = CString::parseUrl($uri)) {
			$retval = true;
		}

		//We need to replace &amp; with & for parse_str to work right...
		if (isset ($_parts['query']) && strpos($_parts['query'], '&amp;')) {
			$_parts['query'] = str_replace('&amp;', '&', $_parts['query']);
		}

		$this->_scheme = isset ($_parts['scheme']) ? $_parts['scheme'] : null;
		$this->_user = isset ($_parts['user']) ? $_parts['user'] : null;
		$this->_pass = isset ($_parts['pass']) ? $_parts['pass'] : null;
		$this->_host = isset ($_parts['host']) ? $_parts['host'] : null;
		$this->_port = isset ($_parts['port']) ? $_parts['port'] : null;
		$this->_path = isset ($_parts['path']) ? $_parts['path'] : null;
		$this->_query = isset ($_parts['query'])? $_parts['query'] : null;
		$this->_fragment = isset ($_parts['fragment']) ? $_parts['fragment'] : null;

		//parse the query

		if (isset($_parts['query'])) {
			parse_str($_parts['query'], $this->_vars);
		}

		return $retval;
	}

	/**
	 * Magic method to get the string representation of the URI object.
	 *
	 * @return	string
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Returns the root URI for the request.
	 *
	 * @param	boolean $pathonly If false, prepend the scheme, host and port information. Default is false..
	 * @return	string	The root URI string.
	 */
	/*public static function root($pathonly = false, $path = null)
	{
		static $root;

		// Get the scheme
		if (!isset($root))
		{
			$uri			= self::getInstance(self::base());
			$root['prefix'] = $uri->toString(array('scheme', 'host', 'port'));
			$root['path']	= rtrim($uri->toString(array('path')), '/\\');
		}

		// Get the scheme
		if (isset($path)) {
			$root['path']	= $path;
		}

		return $pathonly === false ? $root['prefix'].$root['path'].'/' : $root['path'];
	}*/

	/**
	 * Returns the URL for the request, minus the query.
	 *
	 * @return	string
	 */
	/*public static function current()
	{
		static $current;

		// Get the current URL.
		if (!isset($current))
		{
			$uri	= self::getInstance();
			$current = $uri->toString(array('scheme', 'host', 'port', 'path'));
		}

		return $current;
	}*/

	/**
	 * Returns full uri string.
	 *
	 * @access	public
	 * @param	array $parts An array specifying the parts to render.
	 * @return	string The rendered URI string.
	 */
	public function toString($parts = array('scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment'))
	{
		$query = $this->getQuery(); //make sure the query is created

		$uri = '';
		$uri .= in_array('scheme', $parts)  ? (!empty($this->_scheme) ? $this->_scheme.'://' : '') : '';
		$uri .= in_array('user', $parts)	? $this->_user : '';
		$uri .= in_array('pass', $parts)	? (!empty ($this->_pass) ? ':' : '') .$this->_pass. (!empty ($this->_user) ? '@' : '') : '';
		$uri .= in_array('host', $parts)	? $this->_host : '';
		$uri .= in_array('port', $parts)	? (!empty ($this->_port) ? ':' : '').$this->_port : '';
		$uri .= in_array('path', $parts)	? $this->_path : '';
		$uri .= in_array('query', $parts)	? (!empty ($query) ? '?'.$query : '') : '';
		$uri .= in_array('fragment', $parts)? (!empty ($this->_fragment) ? '#'.$this->_fragment : '') : '';

		return $uri;
	}

	/**
	 * Adds a query variable and value, replacing the value if it
	 * already exists and returning the old value.
	 *
	 * @param	string $name Name of the query variable to set.
	 * @param	string $value Value of the query variable.
	 * @return	string Previous value for the query variable.
	 */
	public function setVar($name, $value)
	{
		$tmp = @$this->_vars[$name];
		$this->_vars[$name] = $value;

		//empty the query
		$this->_query = null;

		return $tmp;
	}

	/**
	 * Checks if variable exists.
	 *
	 * @param	string $name Name of the query variable to check.
	 * @return	bool exists.
	 */
	public function hasVar($name)
	{
		return array_key_exists($name, $this->_vars);
	}

	/**
	 * Returns a query variable by name.
	 *
	 * @param	string $name	Name of the query variable to get.
	 * @param	string $default	Default value to return if the variable is not set.
	 * @return	array Query variables.
	 */
	public function getVar($name, $default=null)
	{
		if (array_key_exists($name, $this->_vars)) {
			return $this->_vars[$name];
		}
		return $default;
	}

	/**
	 * Removes an item from the query string variables if it exists.
	 *
	 * @param	string $name Name of variable to remove.
	 */
	public function delVar($name)
	{
		if (array_key_exists($name, $this->_vars))
		{
			unset($this->_vars[$name]);

			//empty the query
			$this->_query = null;
		}
	}

	/**
	 * Sets the query to a supplied string in format:
	 *		foo=bar&x=y
	 *
	 * @param	mixed (array|string) $query The query string.
	 */
	public function setQuery($query)
	{
		if (is_array($query))
		{
			$this->_vars = $query;
		} else {
			if (strpos($query, '&amp;') !== false)
			{
				$query = str_replace('&amp;','&',$query);
			}
			parse_str($query, $this->_vars);
		}

		//empty the query
		$this->_query = null;
	}

	/**
	 * Returns flat query string.
	 *
	 * @return	string Query string.
	 */
	public function getQuery($toArray = false)
	{
		if ($toArray) {
			return $this->_vars;
		}

		//If the query is empty build it first
		if (is_null($this->_query)) {
			$this->_query = self::buildQuery($this->_vars);
		}

		return $this->_query;
	}

	/**
	 * Build a query from a array (reverse of the PHP parse_str()).
	 *
	 * @static
	 * @return	string The resulting query string.
	 * @see	parse_str()
	 */
	public static function buildQuery($params, $akey = null)
	{
		if (!is_array($params) || count($params) == 0) {
			return false;
		}

		return urldecode(http_build_query($params, '', '&'));
	}

	/**
	 * Get URI scheme (protocol)
	 *		ie. http, https, ftp, etc...
	 *
	 * @return	string The URI scheme.
	 */
	public function getScheme()
	{
		return $this->_scheme;
	}

	/**
	 * Set URI scheme (protocol)
	 *		ie. http, https, ftp, etc...
	 *
	 * @param	string $scheme The URI scheme.
	 */
	public function setScheme($scheme)
	{
		$this->_scheme = $scheme;
	}

	/**
	 * Get URI username
	 *		returns the username, or null if no username was specified.
	 *
	 * @return	string The URI username.
	 */
	public function getUser()
	{
		return $this->_user;
	}

	/**
	 * Set URI username.
	 *
	 * @param	string $user The URI username.
	 */
	public function setUser($user)
	{
		$this->_user = $user;
	}

	/**
	 * Get URI password
	 *		returns the password, or null if no password was specified.
	 *
	 * @return	string The URI password.
	 */
	public function getPass()
	{
		return $this->_pass;
	}

	/**
	 * Set URI password.
	 *
	 * @param	string $pass The URI password.
	 */
	public function setPass($pass)
	{
		$this->_pass = $pass;
	}

	/**
	 * Get URI host
	 *		returns the hostname/ip, or null if no hostname/ip was specified.
	 *
	 * @return	string The URI host.
	 */
	public function getHost()
	{
		return $this->_host;
	}

	/**
	 * Set URI host.
	 *
	 * @param	string $host The URI host.
	 */
	public function setHost($host)
	{
		$this->_host = $host;
	}

	/**
	 * Get URI port
	 *		returns the port number, or null if no port was specified.
	 *
	 * @return	int The URI port number.
	 */
	public function getPort()
	{
		return (isset ($this->_port)) ? $this->_port : null;
	}

	/**
	 * Set URI port.
	 *
	 * @param	int $port The URI port number.
	 */
	public function setPort($port)
	{
		$this->_port = $port;
	}

	/**
	 * Gets the URI path string.
	 *
	 * @return	string The URI path string.
	 */
	public function getPath()
	{
		return $this->_path;
	}

	/**
	 * Set the URI path string.
	 *
	 * @param	string $path The URI path string.
	 */
	public function setPath($path)
	{
		$this->_path = $this->_cleanPath($path);
	}

	/**
	 * Get the URI archor string
	 *		everything after the "#".
	 *
	 * @return	string The URI anchor string.
	 */
	public function getFragment()
	{
		return $this->_fragment;
	}

	/**
	 * Set the URI anchor string
	 *		everything after the "#".
	 *
	 * @param	string $anchor The URI anchor string.
	 */
	public function setFragment($anchor)
	{
		$this->_fragment = $anchor;
	}

	/**
	 * Checks whether the current URI is using HTTPS.
	 *
	 * @return	boolean True if using SSL via HTTPS.
	 */
	public function isSSL()
	{
		return $this->getScheme() == 'https' ? true : false;
	}

	/**
	 * Checks if the supplied URL is internal
	 *
	 * @param	string $url The URL to check.
	 * @return	boolean True if Internal.
	 */
	public static function isInternal($url)
	{
		$uri = self::getInstance($url);
		$base = $uri->toString(array('scheme', 'host', 'port', 'path'));
		$host = $uri->toString(array('scheme', 'host', 'port'));
		if (stripos($base, self::base()) !== 0 && !empty($host)) {
			return false;
		}
		return true;
	}

	/**
	 * Resolves //, ../ and ./ from a path and returns
	 * the result. Eg:
	 *
	 * /foo/bar/../boo.php	=> /foo/boo.php
	 * /foo/bar/../../boo.php => /boo.php
	 * /foo/bar/.././/boo.php => /foo/boo.php
	 *
	 * @param	string $uri The URI path to clean.
	 * @return	string Cleaned and resolved URI path.
	 */
	private function _cleanPath($path)
	{
		$path = explode('/', preg_replace('#(/+)#', '/', $path));

		for ($i = 0, $n = count($path); $i < $n; $i ++)
		{
			if ($path[$i] == '.' OR $path[$i] == '..')
			{
				if(($path[$i] == '.') OR ($path[$i] == '..' AND $i == 1 AND $path[0] == ''))
				{
					unset ($path[$i]);
					$path = array_values($path);
					$i --;
					$n --;
				}
				elseif ($path[$i] == '..' AND ($i > 1 OR ($i == 1 AND $path[0] != '')))
				{
					unset ($path[$i]);
					unset ($path[$i -1]);
					$path = array_values($path);
					$i -= 2;
					$n -= 2;
				}
			}
		}

		return implode('/', $path);
	}
}
?>