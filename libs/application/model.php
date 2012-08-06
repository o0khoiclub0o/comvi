<?php
/**
 * Base class for a Joomla Model
 *
 * Acts as a Factory class for application specific objects and
 * provides many supporting API functions.
 *
 * @abstract
 * @package		Comvi.Framework
 * @subpackage	Application
 */
abstract class CModel
{
	/**
	 * The model (base) name
	 *
	 * @var		string
	 */
	protected $name;

	protected $table;
	protected $primaryKey = 'id';
	protected $references = array();
	protected $select = '*';
	protected $where = '';

	protected $fields = array();
	public $errors = array();

	/**
	 * Database Connector
	 *
	 * @var		CDatabase
	 */
	protected $db;


	/**
	 * Constructor
	 */
	public function __construct(&$dbo, $options = array())
	{
		//set the model dbo
		$this->db = $dbo;

		if (isset($options['name']))  {
			$this->name = $options['name'];
		} else {
			$this->name = $this->getName();
		}

		if (isset($options['table']))  {
			$this->table = $options['name'];
		}
	
		if (isset($options['select']))  {
			$this->select = $options['select'];
		}

		if (isset($options['where']))  {
			$this->where = $options['where'];
		}

		if (isset($options['references']))  {
			$this->references = $options['references'];
		}
	}

	/**
	 * Method to get the model name
	 *
	 * The model name by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return	string The name of the model
	 */
	public function getName()
	{
		$name = $this->name;

		if (empty($name)) {
			$r = null;
			if (!preg_match('/(.*)Model/i', get_class($this), $r)) {
				CError::raiseError (500, CText::_('LIB_APPLICATION_ERROR_MODEL_GET_NAME'));
			}
			$name = strtolower($r[1]);
		}

		return $name;
	}

	/**
	 * Get the columns from database table.
	 *
	 * @return	mixed	An array of the field names, or false if an error occurs.
	 */
	public function getFields()
	{
		static $cache = null;

		if ($cache === null) {
			// Lookup the fields for this table only once.
			$name	= $this->table;
			$fields	= $this->db->getTableFields($name, false);

			if (!isset($fields[$name])) {
				throw new CException('LIB_DATABASE_ERROR_COLUMNS_NOT_FOUND');
			}
			$cache = $fields[$name];
		}

		return $cache;
	}

	/**
	 * Customize SELECT to optimize	query
	 *
	 * @param	mixed
	 * @return	CModel	Returns this object to allow chaining.
	 */
	public function filter()
	{
		$arg_list		= func_get_args();
		$this->select	= implode(', ', $arg_list);

		return $this;
	}

	/**
	 * Load datas from database to CModel Object
	 *
	 * @return CModel
	 */
	public function load()
	{
		$query = $this->db->getQuery(true);

		$select = explode(',', $this->select);
		foreach ($select as &$field) {
			$field = '#__'.$this->table.'.'.trim($field);
		}

		foreach ($this->references as $column=>$reference) {
			if (isset($reference['filter'])) {
				$sl = explode(',', $reference['filter']);
				foreach ($sl as $f) {
					$select[] = '#__'.$reference['table'].'.'.trim($f);
				}
			}
			else {
				$select[] = '#__'.$reference['table'].'.*';
			}

			$query->leftJoin('#__'.$reference['table'].' ON #__'.$this->table.'.'.$column.' = #__'.$reference['table'].'.'.$reference['key']);
		}
		$query->select(implode(', ', $select))->from('#__'.$this->table);

		if ($this->where) {
			$query->where($this->where);
		}

		$this->db->setQuery($query);
	}
}
?>