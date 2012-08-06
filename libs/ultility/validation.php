<?php
/**
 * Validation Class
 *
 * @static
 * @package		Comvi.Framework
 * @subpackage	Utilities
 */
abstract class CValidation
{
	public static function required($string)
	{
		return !empty($string);
	}

	public static function email($string)
	{
		return preg_match('/^[^\W][a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}$/', $string);
	}

	public static function email_exist($email)
	{
		$db = CLoader::getDatabase();

		$query = "SELECT id from #__user WHERE `email` = '{$email}' LIMIT 1";
		$db->setQuery($query)->query();

		if ($db->loadResult()) {
			return false;
		}

		return true;
	}
}
?>