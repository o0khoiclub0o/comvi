<?php
/**
 * Class for managing HTTP sessions
 *
 * Provides access to session-state values as well as session-level
 * settings and lifetime management methods.
 * Based on the standart PHP session handling mechanism it provides
 * for you more advanced features such as expire timeouts.
 *
 * @package		Comvi.Framework
 * @subpackage	Session
 */
class CSession
{
	/**
	 * Maximum age of unused session.
	 *
	 * @var	string $expire minutes
	 */
	protected $expire = 15;

	/**
	 * Force cookies to be SSL only
	 *
	 * @default false
	 * @var bool $force_ssl
	 */
	protected $force_ssl = false;


	/**
	 * Constructor
	 *
	 * @param array	$options	optional parameters
	 */
	public function __construct($options = array())
	{
		// Need to destroy any existing sessions started with session.auto_start
		if (session_id()) {
			session_unset();
			session_destroy();
		}

		// set default sessions save handler
		//ini_set('session.save_handler', 'files');

		// disable transparent sid support
		//ini_set('session.use_trans_sid', '0');

		// set options
		// set name
		if (isset($options['name'])) {
			session_name($options['name']);
		}

		// set id
		if (isset($options['id'])) {
			session_id($options['id']);
		}

		// set expire time
		if (isset($options['expire'])) {
			$this->expire = $options['expire'];
		}
	
		// how long an unused PHP session will be kept alive
		ini_set('session.gc_maxlifetime', $this->expire);

		if (isset($options['force_ssl'])) {
			$this->force_ssl = (bool) $options['force_ssl'];
		}

		// Set session cookie parameters
		// get cookie parameters
		$cookie	= session_get_cookie_params();
		if ($this->force_ssl == true) {
			$cookie['secure'] = true;
		}
		if (isset($options['lifetime'])) {
			$cookie['lifetime'] = (int) $options['lifetime'];
		}
		if (isset($options['domain'])) {
			$cookie['domain'] = $options['domain'];
		}
		if (isset($options['path'])) {
			$cookie['path'] = $options['path'];
		}
		session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure']);

		session_start();
	}

	/**
	 * Session object destructor
	 */
	public function __destruct()
	{
		session_write_close();
	}

	/**
	 * Get data from the session store
	 *
	 * @param	string  Name of a variable
	 * @param	mixed	Default value of a variable if not set
	 * @return  mixed	Value of a variable
	 */
	public function get($name, $default = null)
	{

		if (isset($_SESSION[$name])) {
			return $_SESSION[$name];
		}

		return $default;
	}

	/**
	 * Set data into the session store.
	 *
	 * @param	string  Name of a variable.
	 * @param	mixed	Value of a variable.
	 * @return  mixed	Old value of a variable.
	 */
	public function set($name, $value = null)
	{
		$old = isset($_SESSION[$name]) ?  $_SESSION[$name] : null;

		if ($value === null) {
			unset($_SESSION[$name]);
		} else {
			$_SESSION[$name] = $value;
		}

		return $old;
	}

	/**
	 * Get session name
	 *
	 * @return string The session name
	 */
	/*public function getName()
	{
		return session_name();
	}*/

	/**
	 * Get session id
	 *
	 * @return string The session name
	 */
	/*public function getId()
	{
		return session_id();
	}*/
}
?>