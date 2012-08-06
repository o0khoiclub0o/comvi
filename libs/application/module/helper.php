<?php
// Import library dependencies
//jimport('joomla.application.component.helper');

/**
 * Module helper class
 *
 * @static
 * @package		Comvi.Framework
 * @subpackage	Application
 */
abstract class CModuleHelper
{
	/**
	 * Get module by name (real, eg 'Breadcrumbs' or folder, eg 'mod_breadcrumbs')
	 *
	 * @param	string	The name of the module
	 *
	 * @return	object	The Module object
	 */
	public static function &getModule($name)
	{
		$result		= null;
		$modules	= self::_load();
		foreach ($modules AS $module) {
			// Match the name of the module
			if ($module->position == $name)
			{
				return $module;
			}
		}

		return $result;
	}

	/**
	 * Load published modules
	 *
	 * @return	array
	 */
	protected static function &_load()
	{
		static $modules;

		if (!isset($modules)) {
			$asset_id 	= 1;
			$lang 		= CLoader::getLanguage()->getTag();
			$cache 		= CLoader::getCache('modules');
			$cacheid 	= md5(serialize(array($asset_id, $lang)));

			if (!($modules = $cache->get($cacheid))) {
				$db	= CLoader::getDbo();
				$query = $db->getQuery(true);
				$query->select('m.id, m.title, m.content, m.position, m.module');
				//$query->from('#__module_asset AS ma');
				$query->from('#__modules AS m');
				//$query->where('ma.asset_id = '.$asset_id);
				// Filter by language
				$query->where('m.language IN ('.$db->Quote($lang) . ',' . $db->Quote('*').')');
				$query->where('m.published = 1');
				$query->order('m.position, m.ordering');
				// Set the query
				$db->setQuery($query);
				$modules = $db->loadObjectList();

				$clean = array();
				if($db->getErrorNum()){
					CError::raiseWarning(500, CText::sprintf('LIB_APPLICATION_ERROR_MODULE_LOAD', $db->getErrorMsg()));
					return $clean;
				}

				$cache->store($modules, $cacheid);
			}
		}

		return $modules;
	}

	/**
	 * Render the module.
	 *
	 * @param	object	A module object.
	 *
	 * @return	strign	The HTML content of the module output.
	 */
	public static function renderModule($module)
	{
		$app		= CLoader::getApplication();
		$scope		= $app->scope;		// Record the scope.
		$app->scope	= $module->module;	// Set scope to component name


		$lang = CLoader::getLanguage();
			$lang->load('mod.'.$module->module, null, false)
		||	$lang->load('mod.'.$module->module, $lang->getDefault(), false);

		$path = PATH_MODULES.$module->module.DS.$module->module.'.php';

		$contents = null;

		// Execute the component.
		ob_start();
		require $path;
		$contents = ob_get_contents();
		ob_end_clean();


		$app->scope = $scope; //revert the scope

		return $contents;
	}
}
?>
