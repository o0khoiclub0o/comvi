<?php
/**
 * Module renderer
 *
 * @package		Comvi.Framework
 * @subpackage	Document.Renderer
 */
class CDocumentRendererModule
{
	/**
	 * Renders a module script and returns the results as a string
	 *
	 * @param	array	Associative array of values
	 * @return	string	The output
	 */
	public function render($name)
	{
		CLoader::import('application.module.helper', 1);
		$module		= CModuleHelper::getModule($name);
		$contents	= CModuleHelper::renderModule($module);

		return $contents;
	}
}
