<?php
/**
 * Base class for a Controller
 *
 * Controller (controllers are where you put all the actual code) Provides basic
 * functionality, such as rendering views (aka displaying templates).
 */
class CController
{
	/**
	 * The name of the controller
	 */
	protected $name;

	/**
	 * Array of class methods
	 *
	 * @var		array
	 */
	protected $methods = array();

	/**
	 * Current or most recent task to be performed.
	 *
	 * @var		string
	 */
	protected $task;

	protected $return;	


	/**
	 * Constructor.
	 *
	 * @param	CApplication
	 * @param	array An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 */
	public function __construct($options = array())
	{
		//inits
		$this->config	= CLoader::getConfig();
		$this->input	= CLoader::getInput();
		$this->output	= CLoader::getOutput();

		// set the component name and loader
		$r = null;
		if (!preg_match('/(.*)Controller/i', get_class($this), $r)) {
			throw new Exception('LIB_APPLICATION_ERROR_CONTROLLER_GET_NAME', 500);
		}

		$this->name	= strtolower($r[1]);

		// Determine the methods to exclude from the base class.
		$xMethods = get_class_methods('CController');

		// Get the public methods in this class using reflection.
		$r			= new ReflectionClass($this);
		$rName		= $r->getName();
		$rMethods	= $r->getMethods(ReflectionMethod::IS_PUBLIC);
		$methods	= array();

		foreach ($rMethods as $rMethod)
		{
			$mName = $rMethod->getName();

			// Add default display method if not explicitly declared.
			if (!in_array($mName, $xMethods) || $mName == 'display') {
				$this->methods[] = strtolower($mName);
			}
		}


		// Set the task.
		if (array_key_exists('task', $options)) {
			$this->task	= $options['task'];
		}
		elseif ($task = $this->input->get('task')) {
			$this->task = $task;
		}
		elseif (empty($this->task)) {
			$this->task = 'display';
		}

		$this->return = new StdClass();
		$this->return->code = 1;
	}

	/**
	 * Execute a task by triggering a method in the derived class.
	 *
	 * @return	mixed|false The value returned by the called method, false in error case.
	 */
	public function execute()
	{
		$task = $this->task;

		if (!in_array($task, $this->methods)) {
			throw new Exception("LIB_APPLICATION_ERROR_TASK_NOT_FOUND|$task", 404);
		}

		if (!$this->authorise()) {
			throw new Exception('LIB_APPLICATION_ERROR_ACCESS_FORBIDDEN', 403);
		}

		$this->$task();

		return $this->return;
	}

	/**
	 * Authorisation check
	 *
	 * @return	boolean	True if authorised
	 */
	protected function authorise()
	{
		return true;
	}

	/**
	 * Typical view method for MVC based architecture
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @returns	void
	 */
	public function display()
	{
		$layout = $this->input->get('layout');
		$view = $this->getView($layout);

		// Get/Create the model
		if ($model = $this->getModel()) {
			$this->output->setVars('content', $model);
		}

		$this->output->setBody('content', $view);
	}

	public function insert()
	{
		/*$this->load->helper('url');
		
		$slug = url_title($this->input->post('title'), 'dash', TRUE);
		
		$data = array(
			'title' => $this->input->post('title'),
			'slug' => $slug,
			'text' => $this->input->post('text')
		);
		
		return $this->db->insert('news', $data);*/
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	The model name. Optional.
	 * @return	CModel	The model.
	 */
	protected function getModel($name = '')
	{
		if (empty($name)) {
			$name = $this->name;
		}

		$path	= PATH_MODELS.$name.'.php';
		$class	= $name.'Model';

		if (!file_exists($path)) {
			return false;
		}

		require_once $path;
		if (!class_exists($class)) {
			throw new Exception("LIB_APPLICATION_ERROR_MODEL_CLASS_NOT_FOUND|$class|$path");
		}

		$db = CLoader::getDatabase();
		return new $class($db);
	}

	/**
	 * Method to get a view object, loading it if required.
	 *
	 * @param	string	The view layout. Optional.
	 * @return	CView	The view.
	 */
	protected function getView($layout = '')
	{
		$path	= PATH_VIEWS.$this->name.'.php';
		$class	= $this->name.'View';

		if (!file_exists($path)) {
			throw new Exception("LIB_APPLICATION_ERROR_VIEW_CLASS_NOT_FOUND|$class|$path");
		}

		require_once $path;
		if (!class_exists($class)) {
			throw new Exception("LIB_APPLICATION_ERROR_VIEW_CLASS_NOT_FOUND|$class|$path");
		}

		return new $class(array('template' => $this->config->get('template', 'default'), 'layout' => $layout));
	}
}
?>