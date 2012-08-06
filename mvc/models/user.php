<?php
CLoader::import('application.model.item', 1);

class UserModel extends CModelItem
{
	protected $table = 'user';

	protected $fields = array(
		'email' => array(),
		'password' => array(),
		'salt' => array(),
		'fullname' => array(),
		'params' => array()
	);


	/**
	 * Method to login user
	 *
	 * @return bool
	 */
	public function login()
	{
		$query = "SELECT id from #__user WHERE `email` = '{$this->item->email}' AND `password` = MD5(CONCAT('{$this->item->password}', salt)) LIMIT 1";
		$this->db->setQuery($query)->query();

		if ($id = $this->db->loadResult()) {
			$this->item->id = $id;
			return true;
		}

		return false;
	}

	public function load()
	{
		if ($this->getKey() === 0) {
			$this->item->params = new CRegistry;
			return $this;
		}

		return parent::load();
	}
}
?>