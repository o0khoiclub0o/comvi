<?php
/**
 * Abstract Format for CRegistry
 *
 * @abstract
 * @package		Comvi.Framework
 * @subpackage	Registry
 */
abstract class CRegistryFormat
{
	/**
	 * Returns a reference to a Format object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param	string	The format to load
	 * @return	object	Registry format handler
	 * @throws	CException
	 */
	public static function getInstance($type)
	{
		// Initialize static variable.
		static $instances;
		if (!isset ($instances)) {
			$instances = array ();
		}

		// Sanitize format type.
		$type = strtolower($type);

		// Only instantiate the object if it doesn't already exist.
		if (!isset($instances[$type])) {
			// Only load the file the class does not exist.
			$path = dirname(__FILE__).DS.'format'.DS.$type.'.php';

			require $path;

			$class = 'CRegistryFormat'.$type;
			$instances[$type] = new $class();
		}
		return $instances[$type];
	}

	/**
	 * Converts an object into a formatted string.
	 *
	 * @param	object	Data Source Object.
	 * @param	array	An array of options for the formatter.
	 * @return	string	Formatted string.
	 */
	abstract public function objectToString($object, $options = null);

	/**
	 * Converts a formatted string into an object.
	 *
	 * @param	string	Formatted string
	 * @param	array	An array of options for the formatter.
	 * @return	object	Data Object
	 * @since	1.5
	 */
	abstract public function stringToObject($data, $options = null);
}
?>