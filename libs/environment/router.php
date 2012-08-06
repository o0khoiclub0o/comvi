<?php
/**
 * Set the available masks for the routing mode
 */
define('ROUTER_MODE_RAW', 0);
define('ROUTER_MODE_SEF', 1);

/**
 * Class to create and parse routes
 *
 * @package		Comvi.Framework
 * @subpackage	Application
 */
class CRouter
{
	/**
	 * An array of variables
	 *
	 * @var array
	 */
	protected $_vars = array();

	protected $base = array();

	/**
	 * The rewrite mode
	 *
	 * @var integer
	 */
	protected $mode = ROUTER_MODE_RAW;

	/**
	 * The rewrite mode
	 *
	 * @var integer
	 */
	protected $suffix = '';

	/**
	 * The default controller
	 *
	 * @var string
	 */
	protected $default_controller = 'index';


	public function __construct($options = array())
	{
		if (array_key_exists('base', $options)) {
			$this->base = $options['base'];
		}

		if (array_key_exists('mode', $options)) {
			$this->mode = $options['mode'];
		}

		if (array_key_exists('suffix', $options)) {
			$this->suffix = $options['suffix'];
		}

		if (array_key_exists('default_controller', $options)) {
			$this->default_controller = $options['default_controller'];
		}
	}

	/**
	 * Returns the base URI for the request.
	 *
	 * @static
	 * @param	boolean $pathonly If false, prepend the scheme, host and port information. Default is false.
	 * @return	string	The base URI string
	 */
	public function base($pathonly = false)
	{
		return ($pathonly === false) ? $this->base['prefix'].$this->base['path'].'/' : $this->base['path'].'/';
	}

	/**
	 * Parse the URI
	 *
	 * @param	object	The URI
	 *
	 * @return	array
	 */
	public function parse(&$uri)
	{
		// Get site path
		$path = $uri->getPath();
		// Remove the base URI path.
		$path = substr_replace($path, '', 0, strlen($this->base(true)));

		if (substr($path, 0, 9) === 'index.php') {
			$path = ltrim(substr($path, 9), '/');
		}

		if ($suffix = pathinfo($path, PATHINFO_EXTENSION)) {
			$path = str_replace('.'.$suffix, '', $path);
	
			if ($suffix !== $this->suffix) {
				$this->setVar('format', $suffix);
			}
		}

		// Re-set the route (URI)
		$uri->setPath($path);

		if ($path) {
			$vars = explode('/', $path);
			$controller = array_shift($vars);
			$this->setVar('controller', $controller);

			if (!empty($vars)) {
				$this->setVars($vars);
			}
		}
		else {
			$this->setVar('controller', $this->default_controller);
		}

		//$vars = $uri->getQuery(true);
		//$this->setVars($vars);

		return $this->_vars;
	}

	/**
	 * Function to convert an internal URI to a route
	 *
	 * @param	string	The internal URL
	 * @return	string	The absolute search engine friendly URL
	 */
	public function build($url)
	{
		//Create the URI object
		$uri = $this->_createURI($url);

		$this->_buildRawRoute($uri);

		return $uri;
	}

	/**
	 * Create a uri based on a full or partial url string
	 * @return  CURI  A CURI object
	 */
	protected function _createURI($url)
	{
		// Create full URL if we are only appending variables to it
		if (substr($url, 0, 1) == '&') {
			$vars = array();
			if (strpos($url, '&amp;') !== false) {
				$url = str_replace('&amp;','&',$url);
			}

			parse_str($url, $vars);

			$vars = array_merge($this->_vars, $vars);

			foreach($vars as $key => $var) {
				if ($var == "") {
					unset($vars[$key]);
				}
			}

			$url = 'index.php?'.CURI::buildQuery($vars);
		}

		// Decompose link into url component parts
		return new CURI($url);
	}

	/**
	 * Function to build a raw route
	 */
	protected function _buildRawRoute(&$uri)
	{
	}

	/**
	 * Set a router variable, creating it if it doesn't exist
	 *
	 * @param	string	The name of the variable
	 * @param	mixed	The value of the variable
	 * @param	boolean	If True, the variable will be created if it doesn't exist yet
	 */
	public function setVar($key, $value, $create = true)
	{
		if (!$create && array_key_exists($key, $this->_vars)) {
			$this->_vars[$key] = $value;
		} else {
			$this->_vars[$key] = $value;
		}
	}

	/**
	 * Set the router variable array
	 *
	 * @param	array	An associative array with variables
	 * @param	boolean	If True, the array will be merged instead of overwritten
	 */
	public function setVars($vars = array(), $merge = true)
	{
		if ($merge) {
			$this->_vars = array_merge($this->_vars, $vars);
		} else {
			$this->_vars = $vars;
		}
	}

	/**
	 * Get a router variable
	 *
	 * @param	string	The name of the variable
	 * @return  mixed	Value of the variable
	 */
	public function getVar($key)
	{
		$result = null;
		if (isset($this->_vars[$key])) {
			$result = $this->_vars[$key];
		}
		return $result;
	}

	/**
	 * Function to build a sef route
	 */
	/*protected function _buildSefRoute(&$uri)
	{
	}*/

	/**
	 * Process the build uri query data based on custom defined rules
	 */
	/*protected function _processBuildRules(&$uri)
	{
		foreach($this->_rules['build'] as $rule) {
			call_user_func_array($rule, array(&$this, &$uri));
		}
	}*/
}
?>