<?php
CLoader::import('application.model', 1);

/**
 * Model class for handling lists of items.
 *
 * @package		Comvi.Framework
 * @subpackage	Application
 */
class CModelList extends CModel implements ArrayAccess, Iterator, Countable
{
	/**
	 * Array of CModelItem objects
	 *
	 * @var		array
	 */
	protected $items = array();

	/**
	 * Index of Array of CModelItem objects
	 *
	 * @var		int
	 */
	protected $index = 0;


	/**
	 * Constructor.
	 */
	public function __construct(&$dbo, $options = array())
	{
		parent::__construct($dbo, $options);

		if (!isset($this->table)) {
			CLoader::import('ultility.inflector', 1);

			// Guess the singular table name from plural class name
			$this->table = CInflector::tableize($this->name);
		}		
	}

	// declared 4 abstract methods for ArrayAccess interface
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->items[] = $value;
		} else {
			$this->items[$offset] = $value;
		}
	}
	public function offsetExists($offset) {
		return isset($this->items[$offset]);
	}
	public function offsetUnset($offset) {
		unset($this->items[$offset]);
	}
	public function offsetGet($offset) {
		return isset($this->items[$offset]) ? $this->items[$offset] : null;
	}

	// declared 5 abstract methods for Iterator interface
    function rewind() {
		$this->index = 0;
    }
    function current() {
		return $this->items[$this->index];
    }
    function key() {
		return $this->index;
    }
    function next() {
		++$this->index;
    }
    function valid() {
		return isset($this->items[$this->index]);
    }

	// declared 1 abstract methods for Countable interface
	public function count() 
	{ 
		return count($this->items);
	}

	/**
	 * Get specified row
	 * @param	mixed	primary key value
	 *
	 * @return	CModelItem
	 */
	public function row($value)
	{
		CLoader::import('ultility.inflector', 1);

		$classname	= CInflector::camelize($this->table).'Model';
		$filename	= $this->table;
		$component	= explode('_', $this->table);
		$component	= $component[0];

		// Only try to load the class if it doesn't already exist.
		if (!class_exists($classname)) {
			$path = PATH_MODELS.$filename.'.php';

			if (!is_file($path)) {
				throw new Exception("LIB_MODEL_ERROR_FILE_NOT_FOUND|$path");
			}

			// Import the class file.
			require_once $path;

			if (!class_exists($classname)) {
				throw new Exception("LIB_MODEL_ERROR_CLASS_NOT_FOUND_IN_FILE|$classname");
			}
		}

		$options				= array();
		$options['key']			= $value;	
		$options['select']		= $this->select;
		$options['where']		= $this->primaryKey." = '$value'";
		$options['references']	= $this->references;

		// Instantiate a new table class and return it.
		return new $classname($this->db, $options);
	}

	/**
	 * Condition WHERE
	 *
	 * @param	mixed
	 * @return	CModel	Returns this object to allow chaining.
	 */
	public function where()
	{
		$arg_list		= func_get_args();
		$this->where	= implode(' AND ', $arg_list);

		return $this;
	}

	/**
	 * Get first row
	 *
	 * @return	CModelItem
	 */
	public function first()
	{
		CLoader::import('ultility.inflector', 1);

		$classname	= CInflector::camelize($this->table).'Model';
		$filename	= $this->table;
		$component	= explode('_', $this->table);
		$component	= $component[0];

		// Only try to load the class if it doesn't already exist.
		if (!class_exists($classname)) {
			$path = PATH_COMPONENTS.$component.DS.'models'.DS.$filename.'.php';

			if (!is_file($path)) {
				throw new Exception("LIB_MODEL_ERROR_FILE_NOT_FOUND|$path");
			}

			// Import the class file.
			require_once $path;

			if (!class_exists($classname)) {
				throw new Exception("LIB_MODEL_ERROR_CLASS_NOT_FOUND_IN_FILE|$classname");
			}
		}

		$options				= array();
		$options['select']		= $this->select;
		$options['where']		= $this->where;
		$options['references']	= $this->references;

		// Instantiate a new table class and return it.
		return new $classname($this->db, $options);
	}

	/**
	 * Load datas from database to CModelList Object
	 *
	 * @return CModelList
	 */
	public function load()
	{
		parent::load();

		$items = $this->db->loadObjectList();
print_r($items); die();
		if ($items !== null) {
			$item = $items[0];
			if (isset($item->params)) {
				foreach ($this->items as $item) {
					$item->params = new CRegistry($item->params);
				}
			}
		}

		return $this;
	}
}
?>