<?php
/**
 * Base class for a Comvi View
 *
 * Class holding methods for displaying presentation data.
 *
 * @abstract
 * @package		Comvi.Framework
 * @subpackage	Application
 */
class CView
{
	/**
	 * The name of the view
	 *
	 * @var		array
	 */
	protected $name = null;

	/**
	 * The name of the template
	 *
	 * @var		array
	 */
	protected $template = 'default';

	/**
	* Path to template folder
	*
	* @var string
	*/
	protected $basePath;

	/**
	 * Layout name
	 *
	 * @var		string
	 */
	protected $layout;

	/**
	 * Layout ext
	 *
	 * @var		string
	 */
	protected $layoutExt = '.php';

	protected $vars;

	/**
	 * Constructor
	 */
	function __construct($options = array())
	{
		if (isset($options['template'])) {
			$this->template	= $options['template'];
		}

		$r = null;
		if (!preg_match('/(.*)View$/i', get_class($this), $r)) {
			throw new Exception('LIB_APPLICATION_ERROR_VIEW_GET_NAME');
		}

		if ($r[1] !== 'C') {
			$this->name		= strtolower($r[1]);
			$this->basePath	= 'layouts'.DS;
		}
		else {
			$this->name		= CLoader::getConfig()->get('application');
			$this->basePath	= '';
		}

		$this->layout = $this->name;

		if (!empty($options['layout']))  {
			$this->layout	.= '_'.$options['layout'];
		}

		//if (isset($options['theme_path']))  {
		//	$this->basePath = $options['theme_path'];
		//}

		$this->vars			= &CLoader::getOutput()->getVars();
	}

	/**
	* Assign variable for the view (by reference).
	*
	* You are not allowed to set variables that begin with an underscore;
	* these are either private properties for JView or private variables
	* within the template script itself.
	*
	* <code>
	* $view = new CView();
	*
	* // assign by name and value
	* $view->assignRef('var1', $ref);
	*
	* // assign directly
	* $view->ref = &$var1;
	* </code>
	*
	* @access public
	*
	* @param string The name for the reference in the view.
	* @param mixed The referenced variable.
	*
	* @return bool True on success, false on failure.
	*/
	function assignRef($key, &$val)
	{
		if (is_string($key) && substr($key, 0, 1) != '_')
		{
			$this->$key = &$val;
			return true;
		}

		return false;
	}

	/**
	* Execute and display a template script.
	*
	* @param string The name of the template file to parse;
	* automatically searches through the template paths.
	*
	* @throws object An CError object.
	* @see fetch()
	*/
	/*function display($layout = null)
	{
		$this->load($layout);
	}*/

	/**
	 * Load a template file -- first look in the templates folder for an override
	 *
	 * @access	public
	 * @param string The name of the template source file ...
	 * automatically searches the template paths and compiles as needed.
	 * @return string The output of the the template script.
	 */
	function load($layout = null)
	{
		if ($layout == null) {
			$layout = $this->layout;
		}

		$path = PATH_THEMES.$this->template.DS.$this->basePath.$layout.$this->layoutExt;

		// include the requested template filename in the local scope
		// (this will execute the view logic).
		require_once $path;
	}

	/**
	 * Render a web component
	 *
	 * @access	public
	 * @param string The name of the component
	 * @return string The output of the component
	 */
	function render($name)
	{
		$content = CLoader::getOutput()->getBody($name);

		if ($content instanceof CView) {
			$content->load();
		}
		else {
			echo $content;
		}
	}
}
?>