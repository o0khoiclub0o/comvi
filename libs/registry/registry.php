<?php
/**
 * CRegistry class
 *
 * @package		Comvi.Framework
 * @subpackage	Registry
 */
class CRegistry
{
	/**
	 * Registry Object
	 *
	 * @var object
	 */
	protected $data;


	/**
	 * Constructor
	 */
	public function __construct($data = null)
	{
		// Instantiate the internal data object.
		$this->data = new stdClass();

		// Optionally load supplied data.
		if (is_array($data) || is_object($data)) {
			$this->loadObject($data);
		}
		elseif (!empty($data) && is_string($data)) {
			$this->loadString($data);
		}
	}

	/**
	 * Load a string into the registry
	 *
	 * @param	string	string to load into the registry
	 * @param	string	format of the string
	 * @return	boolean	True on success
	 */
	public function loadString($data, $format = 'JSON')
	{
		CLoader::import('registry.format', 1);

		// Load a string into the given namespace [or default namespace if not given]
		$handler = CRegistryFormat::getInstance($format);

		$obj = $handler->stringToObject($data);
		$this->loadObject($obj);

		return true;
	}

	/**
	 * Load a string into the registry
	 *
	 * @param	mixed	$data	An array or object of data to load into the registry
	 * @return	boolean	True on success
	 */
	public function loadObject($data)
	{
		$this->bindData($this->data, $data);

		return true;
	}

	/**
	 * Method to recursively bind data to a parent object.
	 *
	 * @param	object	$parent	The parent object on which to attach the data values.
	 * @param	mixed	$data	An array or object of data to bind to the parent object.
	 *
	 * @return	void
	 */
	protected function bindData(& $parent, $data)
	{
		// Ensure the input data is an array.
		if(is_object($data)) {
			$data = get_object_vars($data);
		} else {
			$data = (array) $data;
		}

		foreach ($data as $k => $v) {
			if (is_array($v) || is_object($v)) {
				if (!isset($parent->$k)) {
					$parent->$k = new stdClass();
				}
				$this->bindData($parent->$k, $v);
			} else {
				$parent->$k = $v;
			}
		}
	}

	/**
	 * Get a registry value.
	 *
	 * @param	string	Registry path (e.g. joomla.content.showauthor)
	 * @param	mixed	Optional default value, returned if the internal value is null.
	 * @return	mixed	Value of entry or null
	 */
	public function get($path, $default = null)
	{
		// Initialise variables.
		$result = $default;

		if(!strpos($path, '.'))
		{
			return (isset($this->data->$path) && $this->data->$path !== null && $this->data->$path !== '') ? $this->data->$path : $default;
		}
		// Explode the registry path into an array
		$nodes = explode('.', $path);

		// Initialize the current node to be the registry root.
		$node = $this->data;
		$found = false;
		// Traverse the registry to find the correct node for the result.
		foreach ($nodes as $n) {
			if (isset($node->$n)) {
				$node = $node->$n;
				$found = true;
			} else {
				$found = false;
				break;
			}
		}
		if ($found && $node !== null && $node !== '') {
			$result = $node;
		}

		return $result;
	}

	/**
	 * Set a registry value.
	 *
	 * @param	string	Registry Path (e.g. joomla.content.showauthor)
	 * @param 	mixed	Value of entry
	 * @return 	mixed	The value of the that has been set.
	 */
	public function set($path, $value)
	{
		$result = null;

		// Explode the registry path into an array
		if ($nodes = explode('.', $path)) {
			// Initialize the current node to be the registry root.
			$node = $this->data;

			// Traverse the registry to find the correct node for the result.
			for ($i = 0, $n = count($nodes) - 1; $i < $n; $i++) {
				if (!isset($node->$nodes[$i]) && ($i != $n)) {
					$node->$nodes[$i] = new stdClass();
				}
				$node = $node->$nodes[$i];
			}

			// Get the old value if exists so we can return it
			$result = $node->$nodes[$i] = $value;
		}

		return $result;
	}

	/**
	 * Sets a default value if not alreay assigned.
	 *
	 * @param	string	The name of the parameter.
	 * @param	string	An optional value for the parameter.
	 * @return	string	The value set, or the default if the value was not previously set (or null).
	 */
	public function def($key, $default = '')
	{
		$value = $this->get($key, (string) $default);
		$this->set($key, $value);
		return $value;
	}

	/**
	 * Load the contents of a file into the registry
	 *
	 * @param	string	Path to file to load
	 * @param	string	Format of the file [optional: defaults to JSON]
	 * @param	mixed	Options used by the formatter
	 * @return	boolean	True on success
	 */
	/*public function loadFile($file, $format = 'JSON', $options = array())
	{
		// Get the contents of the file
		jimport('joomla.filesystem.file');
		$data = JFile::read($file);

		return $this->loadString($data, $format, $options);
	}*/

	/**
	 * Merge a CRegistry object into this one
	 *
	 * @param	object	Source CRegistry object ot merge
	 * @return	boolean	True on success
	 */
	/*public function merge(&$source)
	{
		if ($source instanceof CRegistry) {
			// Load the variables into the registry's default namespace.
			foreach ($source->toArray() as $k => $v) {
				if (($v !== null) && ($v !== '')){
					$this->data->$k = $v;
				}
			}
			return true;
		}
		return false;
	}*/

	/**
	 * Transforms a namespace to an array
	 *
	 * @param	string	Namespace to return [optional: null returns the default namespace]
	 * @return	array	An associative array holding the namespace data
	 */
	/*public function toArray()
	{
		return (array) $this->asArray($this->data);
	}*/

	/**
	 * Method to recursively convert an object of data to an array.
	 *
	 * @param	object	$data	An object of data to return as an array.
	 *
	 * @return	array	Array representation of the input object.
	 */
	protected function asArray($data)
	{
		$array = array();

		foreach (get_object_vars((object) $data) as $k => $v) {
			if (is_object($v)) {
				$array[$k] = $this->asArray($v);
			} else {
				$array[$k] = $v;
			}
		}

		return $array;
	}

	/**
	 * Get a namespace in a given string format
	 *
	 * @param	string	Format to return the string in
	 * @param	mixed	Parameters used by the formatter, see formatters for more info
	 * @return	string	Namespace in string format
	 */
	public function toString($format = 'JSON', $options = array())
	{
		CLoader::import('registry.format', 1);

		// Return a namespace in a given format
		$handler = CRegistryFormat::getInstance($format);
		
		return $handler->objectToString($this->data, $options);
	}
}
?>