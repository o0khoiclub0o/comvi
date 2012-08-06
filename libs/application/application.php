<?php

class CApplication
{
	protected $name;

	//protected $config;

	//protected $client = null;
	// protected $_messageQueue = array();

	/**
	 * The scope of the application.
	 *
	 * @var		string
	 */
	public $scope;

	/**
	 * The time the request was made as Unix timestamp.
	 *
	 * @var		integer
	 */
	protected $startTime;

	/**
	 * Class constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 */
	public function __construct($options = array())
	{
		// set exception handler
		set_exception_handler(array($this, 'exception_handler'));

		$this->config	= CLoader::getConfig();
		$this->input	= CLoader::getInput();
		$this->router	= CLoader::getRouter();
		$this->output	= CLoader::getOutput();

		if ($this->config->get('timezone', '') != '') {
			$tz = $this->config->get('timezone');
		}
		//set the time when the application start
		CLoader::import('ultility.date');
		$now = new CDate('micro', $tz);
		$this->startTime = $now->toUnix();

		if (!isset($this->name)) {
			$r = null;
			if (!preg_match('/C(.*)/i', get_class($this), $r)) {
				throw new CException('LIB_APPLICATION_ERROR_APPLICATION_GET_NAME');
			}
			$this->name = strtolower($r[1]);
		}

		$this->config->set('application', $this->name);
		//$this->config->set('app_starttime', $this->startTime);

		// overwrite configs by options array
		foreach ($options as $k=>$v) {
			$this->config->set($k, $v);
		}
	}

	public function exception_handler($exception)
	{
		CLoader::import('error.exception', 1);

		if ((bool) $this->config->get('debug', false) == true) {
			$msg = sprintf("Uncaught exception '%s': '%s' in %s:%s\n",
				$exception->getCode(),
				$exception->getMessage(),
				$exception->getFile(),
				$exception->getLine());
		} else {
			$msg = sprintf("Uncaught exception: '%s'",
				$exception->getMessage());
		}

		echo $msg;
	}

	/**
	 * Execute the application
	 *
	 * @return	void
	 */
	public function execute()
	{
		// Initialise the application.
		$this->initialise();

		// Route the application.
		$this->route();

		// Dispatch the application.
		$this->dispatch();
	}

	/**
	 * Initialise the application.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 */
	protected function initialise()
	{
		if ((bool) $this->config->get('session', false) === true) {
			CLoader::getSession();
		}
	}

	/**
	 * Route the application.
	 *
	 * Routing is the process of examining the request environment to determine which
	 * component should receive the request. The component optional parameters
	 * are then set in the request object to be processed when the application is being
	 * dispatched.
	 */
	protected function route()
	{
		// Get the full request URI.
		$uri = $this->input->uri;

		// Do redirect if necessary
		if ((bool) $this->config->get('force_ssl', false) === true && strtolower($uri->getScheme()) != 'https') {
			//forward to https
			$uri->setScheme('https');
			$this->redirect((string) $uri);
		}

		$result = $this->router->parse($uri);

		$this->input->setVars($result, 'get');
	}

	/**
	 * Dispatch the application.
	 *
	 * Dispatching is the process of pulling the component name from the request object and
	 * mapping them to a component. If the component does not exist, it handles
	 * determining a default component to dispatch.
	 *
	 * @return	void
	 */
	protected function dispatch()
	{
		$controller	= $this->input->get('controller');
		$scope		= $this->scope;		// record the scope
		$this->scope= $controller;		// set scope to controller name

		// Define component path.
		$path	= PATH_CONTROLLERS.$controller.'.php';
		$class	= $controller.'Controller';

		// If component disabled throw error
		if (!file_exists($path)) {
			throw new Exception("LIB_APPLICATION_ERROR_CONTROLLER_NOT_FOUND|$path", 404);
		}

		require $path;
		if (!class_exists($class)) {
			throw new Exception("LIB_APPLICATION_ERROR_CONTROLLER_CLASS_NOT_FOUND|$class|$path");
		}
		$controller = new $class;
		$return = $controller->execute();

		$this->scope= $scope;		// revert the scope

		switch ($return->code) {
			case 0:
				$this->close();
				break;
			case 1:
				$this->display();
				break;
			case 2:
				$this->redirect($return->url);
				break;
			default:
				throw new Exception('LIB_APPLICATION_ERROR_CONTROLLER_RETURN_CODE_UNKNOWN');
		}
	}

	/**
	 * Exit the application.
	 *
	 * @param	int	Exit code
	 */
	protected function close($code = 0)
	{
		exit($code);
	}

	/**
	 * Redirect to another URL.
	 *
	 * Optionally enqueues a message in the system message queue (which will be displayed
	 * the next time a page is loaded) using the enqueueMessage method. If the headers have
	 * not been sent the redirect will be accomplished using a "301 Moved Permanently"
	 * code in the header pointing to the new location. If the headers have already been
	 * sent this will be accomplished using a JavaScript statement.
	 *
	 * @param	string	The URL to redirect to. Can only be http/https URL
	 * @param	boolean	True if the page is 301 Permanently Moved
	 * @return	none; calls exit().
	 */
	protected function redirect($url, $moved = false)
	{
		// Check for relative internal links.
		if (!preg_match('#^http#i', $url)) {
			$url = $this->router->base().$url;
		}

		// If the headers have been sent, then we cannot send an additional location header
		// so we will output a javascript redirect statement.
		if (headers_sent()) {
			echo "<script>document.location.href='$url';</script>\n";
		}
		else {
			$document = CLoader::getDocument();

			if (mb_detect_encoding($url, 'ASCII', true)) {
				// MSIE type browser and/or server cause issues when url contains utf8 character,so use a javascript redirect method
 				echo '<html><head><meta http-equiv="content-type" content="text/html; charset='.$document->charset.'" /><script>document.location.href=\''.$url.'\';</script></head><body></body></html>';
			}
			else {
				// All other browsers, use the more efficient HTTP header method
				if ($moved == true) {
					header('HTTP/1.1 301 Moved Permanently');
				}
				header('Location: '.$url);
				header('Content-Type: text/html; charset='.$document->charset);
			}
		}

		$this->close();
	}

	/**
	 * Outputs the document
	 *
	 * @access public
	 * @param boolean	$cache		If true, cache the output
	 * @param boolean	$compress	If true, compress the output
	 * @param array		$params		Associative array of attributes
	 * @return	The rendered data
	 */
	protected function display()
	{
		//$compress = $this->config->get('gzip', false);

		$document	= CLoader::getDocument();
		$user		= CLoader::getUser()->getItem();

		$this->output->setVars('document', $document);
		$this->output->setVars('user', $user);
		$this->output->setHeader('Content-Type', $document->mime . '; charset=' . $document->charset);
		$this->output->sendHeaders();

		// set header
		//if ($mdate = $document->get('mdate')) {		// get modified date
		//	CResponse::setHeader('Last-Modified', $mdate);
		//}

		// Render the document.
		CLoader::import('application.view', 1);
		$options	= array(
			'template'	=> $this->config->get('template'),
		);
		$view = new CView($options);
		$view->load();
	}
}
?>