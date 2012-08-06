<?php
/**
 * Load and init all the classes of the framework
 */
class CLoader
{
	private static $configs;
	private static $input;
	private static $output;
	private static $session;
	private static $users;
	private static $databases;
	private static $router;
	private static $document;


	/**
	 * Loads a class from specified directories.
	 *
	 * @param string	The class name to look for (dot notation).
	 * @param bool		use require_once or require
	 * @param string	Search this directory for the class.
	 */
	static function import($filePath, $require_once = false, $base = PATH_LIBRARIES)
	{
		if (!$base) {
			$base = PATH_ROOT;
		}
		$path = str_replace('.', DS, $filePath);
		$filename = $base.$path.'.php';
		if (file_exists($filename))
		{
			if ($require_once == false) {
				require $filename;
			}
			else {
				require_once $filename;
			}

			return true;
		}
		echo $filename.' is not found';

		return false;
	}

	/**
	 * Load the config file.
	 * Only creating it if it doesn't already exist.
	 *
	 * @param	string	$type	Type of config
	 *
	 * @return	CRegistry object
	 */
	public static function getConfig($type = null)
	{
		if (!isset(self::$configs)) {
			self::$configs = array();
		}

		$name = ($type) ? $type : 'config';

		if (!isset(self::$configs[$name])) {
			self::import('registry.registry', 1);

			require PATH_CONFIGURATION.$name.'.php';
			$config = new CRegistry($$name);

			//overwrite Config
			if (class_exists($classname = 'CAppConfig'.$type)) {
				$appconfig	= new $classname();
				$config->loadObject($appconfig);
			}

			self::$configs[$name] = &$config;
		}

		return self::$configs[$name];
	}

	/**
	 * Get a input object.
	 * Only creating it if it doesn't already exist.
	 *
	 * @return	CInput object
	 */
	public static function getInput()
	{
		if (!isset(self::$input)) {
			self::import('environment.input');
			self::$input = new CInput();
		}

		return self::$input;
	}

	/**
	 * Get a output object.
	 * Only creating it if it doesn't already exist.
	 *
	 * @return	COutput object
	 */
	public static function getOutput()
	{
		if (!isset(self::$output)) {
			self::import('environment.output');

			$conf = self::getConfig();

			$options = array();

			if ((bool) $conf->get('gzip', false) == true) {
				$options['compress'] = true;
			}

			self::$output = new COutput($options);
		}

		return self::$output;
	}

	/**
	 * Get a session object.
	 * Only creating it if it doesn't already exist.
	 *
	 * @return	CSession object
	 */
	public static function getSession()
	{
		if (!isset(self::$session)) {
			self::import('session.session');

			$conf = self::getConfig();

			$options = array();

			if ($conf->get('force_ssl') == true) {
				$options['force_ssl'] = true;
			}

			if ($conf->get('application', '') != '') {
				$options['name'] = md5($conf->get('application').$conf->get('secret'));
			}

			if ($conf->get('session_expire', '') != '') {
				$options['expire'] = (int) $conf->get('session_expire');
			}

			if ($conf->get('cookie_lifetime', '') != '') {
				$options['lifetime'] = (int) $conf->get('cookie_lifetime');
			}

			if ($conf->get('cookie_domain', '') != '') {
				$options['domain'] = $conf->get('cookie_domain');
			}

			if ($conf->get('cookie_path', '') != '') {
				$options['path'] = $conf->get('cookie_path');
			}

			self::$session = new CSession($options);
		}

		return self::$session;
	}

	/**
	 * Get an user object.
	 * Only creating it if it doesn't already exist.
	 *
	 * @param int $id The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 *
	 * @return CUser object
	 */
	public static function getUser($id = null)
	{
		if (!isset(self::$users)) {
			self::$users = array();
		}

		if ($id === null) {
			$id = self::getSession()->get('user_id', 0);
		}

		if (!isset(self::$users[$id])) {
			self::$users[$id] = self::getDatabase()->users()->row($id)->load();
		}

		return self::$users[$id];
	}

	/**
	 * Get a database object.
	 * Only creating it if it doesn't already exist.
	 *
	 * @param	string	$name	name of connection
	 *
	 * @return	CDatabase object
	 */
	public static function getDatabase($name = null)
	{
		if (!isset(self::$databases)) {
			$database = array();
		}

		if ($name == null) {
			$name = 'default';
		}

		if (!isset(self::$databases[$name])) {
			self::import('database.database');

			//get the database configuration setting
			$conf	= self::getConfig('database');
			$options= get_object_vars($conf->get($name));
			$driver	= array_key_exists('driver', $options) ? $options['driver']	: 'mysql';

			self::import("database.driver.$driver");

			$classname	= 'CDatabase'.$driver;
			$database	= new $classname($options);

			/*if ($error = $database->getErrorMsg()) {
				//CError::setErrorHandling(E_ERROR, 'ignore'); //force error type to die
				return CError::raiseError(500, CText::sprintf('LIB_DATABASE_ERROR_CONNECT_DATABASE', $error));
			}

			if (CError::isError($database)) {
				header('HTTP/1.1 500 Internal Server Error');
				exit('Database Error: ' . (string) $database);
			}

			if ($database->getErrorNum() > 0) {
				CError::raiseError(500, CText::sprintf('LIB_UTIL_ERROR_CONNECT_DATABASE', $db->getErrorNum(), $db->getErrorMsg()));
			}*/

			self::$databases[$name] = &$database;
		}

		return self::$databases[$name];
	}

	/**
	 * Get a route object.
	 * Only creating it if it doesn't already exist.
	 *
	 * @return	CRouter object
	 */
	public static function getRouter()
	{
		if (!isset(self::$router)) {
			self::import('environment.router');

			$conf = self::getConfig('route');

			$options = array();

			if ($conf->get('mode', 0) != 0) {
				$options['mode'] = $conf->get('mode');
			}

			if ($conf->get('suffix', '') != '') {
				$options['suffix'] = $conf->get('suffix');
			}

			if ($conf->get('default_controller', '') != '') {
				$options['default_controller'] = $conf->get('default_controller');
			}

			// guess base uri
			$options['base']['prefix'] = self::getInput()->uri->toString(array('scheme', 'host', 'port'));
			if (strpos(php_sapi_name(), 'cgi') !== false && !ini_get('cgi.fix_pathinfo') && !empty($_SERVER['REQUEST_URI'])) {
				// PHP-CGI on Apache with "cgi.fix_pathinfo = 0"

				// We shouldn't have user-supplied PATH_INFO in PHP_SELF in this case
				// because PHP will not work with PATH_INFO at all.
				$script_name =  $_SERVER['PHP_SELF'];
			}
			else {
				//Others
				$script_name =  $_SERVER['SCRIPT_NAME'];
			}
			$options['base']['path'] =  rtrim(dirname($script_name), '/\\');

			self::$router = new CRouter($options);
		}

		return self::$router;
	}

	/**
	 * Get a document object
	 *
	 * Returns the global {@link CDocument} object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return CDocument object
	 */
	public function getDocument()
	{
		if (!isset(self::$document)) {
			$type = self::getInput()->getWord('format', 'html');
			self::import("document.$type");

			$options = array (
				'charset'	=> 'utf-8',
				'language'	=> 'en',
				'direction'	=> 'ltr',
				'tab'		=> "\t",
				'lineend'	=> 'unix'
			);

			if ($type === 'html') {
				$conf = self::getConfig();

				if ($conf->get('static_url', '') != '') {
					$options['static_url'] = $conf->get('static_url');
				}
			}

			$classname	= 'CDocument'.$type;
			self::$document	= new $classname($options);
		}

		return self::$document;
	}
}
?>