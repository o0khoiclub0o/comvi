<?php
CLoader::import('application.model', 1);

/**
 * Model class for an item.
 *
 * @package		Comvi.Framework
 * @subpackage	Application
 */
class CModelItem extends CModel
{
	/**
	 * Model datas
	 *
	 * @var		StdClass
	 */
	public $item;


	/**
	 * Constructor.
	 */
	public function __construct(&$dbo, $options = array())
	{
		parent::__construct($dbo, $options);

		$this->item = new stdClass();

		if (isset($options['key']))  {
			$this->setKey($options['key']);
		}

		if (!isset($this->table)) {
			CLoader::import('ultility.inflector', 1);

			// Guess the singular table name from singular class name
			$this->table = CInflector::underscore($this->name);
		}
	}

	public function getItem()
	{
		return $this->item;
	}

	protected function setKey($value)
	{
		$k = $this->primaryKey;
		$this->item->$k = $value;
	}

	protected function getKey()
	{
		$k = $this->primaryKey;
		return $this->item->$k;
	}

	public function addValidation($field, $rule, $warn)
	{
		$this->fields[$field]['rules'][$rule] = $warn;
	}

	public function validateForm()
	{
		CLoader::import('ultility.validation', 1);

		foreach ($this->fields as $name=>$field) {
			if (isset($field['rules'])) {
				foreach ($field['rules'] as $key => $val) {
					if (!CValidation::$key($this->item->$name)) {
						$this->errors[] = $val;
					}
				}
			}
		}

		return (empty($this->errors)) ? true : false;
	}

	/**
	 * Load datas from database to CModelItem Object
	 *
	 * @return stdClass
	 */
	public function load()
	{
		parent::load();

		$item = $this->db->loadObject();

		if ($item !== null) {
			foreach (get_object_vars($item) as $key => $value) {
				if ($key == 'params') {
					$item->$key = new CRegistry($value);
				}
			}

			$this->item = $item;
		}

		return $this;
	}

	/**
	 * Insert Model datas to database
	 *
	 * @return CModelItem
	 */
	public function insert()
	{
		if (isset($this->item->params) && $this->item->params instanceof CRegistry) {
			$this->item->params = $this->item->params->toString();
		}

		$ret = $this->db->insertObject('#__'.$this->table, $this->item);

		//Get the new record id
		$id = (int)$this->db->insertid();

		$this->setKey($id);

		return $this;
	}

	/**
	 * Update Model datas to database
	 *
	 * @return CModelItem
	 */
	public function update()
	{
		if (isset($this->item->params) && $this->item->params instanceof CRegistry) {
			$this->item->params = $this->item->params->toString();
		}

		$ret = $this->db->updateObject('#__'.$this->table, $this->item, $this->primaryKey);

		return $this;
	}

	/**
	 * Save Model datas to database
	 *
	 * @return CModelItem
	 */
	public function save()
	{

		// ....

		return $this;
	}
}
?>