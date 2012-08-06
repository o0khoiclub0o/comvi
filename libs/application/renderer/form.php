<?php
/**
 * Form renderer
 *
 * @package		Comvi.Framework
 * @subpackage	Document.Renderer
 */
class CDocumentRendererForm
{
	/**
	 * Renders a form
	 *
	 * @param	array	Associative array of values
	 * @return	string	html form
	 */
	public function render($name)
	{
		CLoader::import('html.form', 1);
		//$module		= CForm::getForm($name);
		$contents	= CForm::render($name);

		return $contents;
	}
}
