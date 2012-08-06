<?php
CLoader::import('application.view', 1);

/**
 * Base class for a Comvi View
 *
 * @abstract
 * @package		Comvi.Framework
 * @subpackage	Application.Module
 */
class modView extends CView
{
	protected $id;
	protected $title;
	protected $content;
	protected $position;
	protected $module;


	/**
	 * Constructor
	 */
	function __construct($module, $config = array())
	{
		$this->id		= $module->id;
		$this->title	= $module->title;
		$this->content	= $module->content;
		$this->position	= $module->position;
		$this->module	= $module->module;

		if (!array_key_exists('layout', $config))  {
			$config['layout']	= 'modules'.DS.$this->module.DS.$this->position;
		}

		parent::__construct($config);
	}
}
?>