<?php
/**
 * Comvi Exception object.
 *
 * @package	Comvi.Framework
 * @subpackage	Error
 */
class CException extends Exception
{
	/**
	 * Error level
	 * @var string
	 */
	protected $level;


	/**
	 * Constructor
	 *	- used to set up the error with all needed error details.
	 *
	 * @access	protected
	 * @param	string	$msg		The error message
	 * @param	string	$code		The error code from the application
	 * @param	int		$level		The error level (use the PHP constants E_ALL, E_NOTICE etc.).
	 * @param	boolean	$backtrace	True if backtrace information is to be collected
	 */
	public function __construct($msg, $code = 0, $level = null)
	{
		$this->level = $level;

		parent::__construct($msg, $code);
	}

	/**
	 * Returns to error message
	 *
	 * @access	public
	 * @return	string Error message
	 */
	public function __toString()
	{
		return $this->message;
	}

	/**
	 * Returns the error level
	 *
	 * @return	E_ALL | E_NOTICE etc.
	 */
	public function getLevel()
	{
		return $this->level;
	}

	/**
	 * Returns an associative array of object properties
	 *
	 * @access	public
	 * @param	boolean $public If true, returns only the public properties
	 * @return	array
	 * @see		get()
	 */
	public function getProperties($public = true)
	{
		$vars  = get_object_vars($this);

		if ($public)
		{
			foreach ($vars as $key => $value)
			{
				if ('_' == substr($key, 0, 1)) {
					unset($vars[$key]);
				}
			}
		}
		return $vars;
	}
}
