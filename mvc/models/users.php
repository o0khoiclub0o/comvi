<?php
CLoader::import('application.model.list', 1);

/**
 * This models supports retrieving lists of users.
 *
 * @package		Comvi.Site
 * @subpackage	com.user
 */
class UsersModel extends CModelList
{
	protected $_table = 'user';
}
?>