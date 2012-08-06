<?php
//import('application.component.controller');

// @TODO Add ability to set redirect manually to better cope with frontend usage.

/**
 * Controller tailored to suit most form-based admin operations.
 *
 * @package		Comvi.Framework
 * @subpackage	Application
 */
class CControllerForm extends CController
{
	/**
	 * @var		string	The URL com for the component.
	 */
	protected $option;

	/**
	 * @var		string	The URL view item variable.
	 */
	protected $view_item;

	/**
	 * @var		string	The URL view list variable.
	 */
	protected $view_list;


	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 *
	 * @return	CControllerForm
	 * @see		CController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Guess the com as NameOfController
		if (empty($this->com)) {
			$this->com = strtolower($this->getName());
		}

		// Guess the item view as the suffix, eg: ContentControllerArticle -> Article
		if (empty($this->view_item)) {
			$r = null;
			if (!preg_match('/(.*)Controller(.*)/i', get_class($this), $r)) {
				CError::raiseError(500, CText::_('LIB_APPLICATION_ERROR_CONTROLLER_GET_ITEM'));
			}
			$this->view_item = strtolower($r[2]);
		}
	
		// Guess the list view as the plural of the item view.
		if (empty($this->view_list)) {
			// For more complex types, just manually set the variable in your class.
			$plural = array(
				array( '/(x|ch|ss|sh)$/i',		"$1es"),
				array( '/([^aeiouy]|qu)y$/i',	"$1ies"),
				array( '/([^aeiouy]|qu)ies$/i',	"$1y"),
				array( '/(bu)s$/i',				"$1ses"),
				array( '/s$/i',					"s"),
				array( '/$/',					"s")
			);

			// check for matches using regular expressions
			foreach ($plural as $pattern)
			{
				if (preg_match($pattern[0], $this->view_item)) {
					$this->view_list = preg_replace( $pattern[0], $pattern[1], $this->view_item);
					break;
				}
			}
		}
	}

	/**
	 * Method to add a new record.
	 *
	 * @return	mixed	True if the record can be added, a CError object if not.
	 */
	public function add()
	{
		CRequest::setVar('view', $this->view_item);
		parent::display();
	}

	/**
	 * Method to edit an existing record.
	 *
	 * @return	Boolean	True if access level check and checkout passes, false otherwise.
	 */
	public function edit()
	{
		CRequest::setVar('view', $this->view_item);
		parent::display();
	}

	/**
	 * Method to cancel an edit.
	 *
	 * @return	Boolean	True if access level checks pass, false otherwise.
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?com='.$this->com.'&view='.$this->view_list);

		return true;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	$name	The model name. Optional.
	 * @param	string	$prefix	The class prefix. Optional.
	 *
	 * @return	object	The model.
	 */
	public function getModel($name = '', $prefix = '')
	{
		if (empty($name)) {
			$name = $this->view_item;
		}

		return parent::getModel($name, $prefix);
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param	int		$recordId	The primary key id for the item.
	 * @param	string	$urlVar		The name of the URL variable for the id.
	 *
	 * @return	string	The arguments to append to the redirect URL.
	 */
	protected function getRedirectAppend($recordId = null, $urlVar = 'id')
	{
		$append		= '';
		/*$layout		= CRequest::getCmd('layout', 'edit');

		if ($layout) {
			$append .= '&layout='.$layout;
		}*/

		if ($recordId) {
			$append .= '&'.$urlVar.'='.$recordId;
		}

		return $append;
	}

	/**
	 * Method to save a record.
	 *
	 * @param	string	$key	The name of the primary key of the URL variable.
	 * @param	string	$urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return	Boolean	True if successful, false otherwise.
	 */
	public function save($key = null, $urlVar = null)
	{
		// Initialise variables.
		//$app		= JFactory::getApplication();

		$model		= $this->getModel();
		$table		= $model->getTable();
		$data		= JRequest::getVar('jform', array(), 'post', 'array');
		$context	= $this->com.'edit'.$this->view_item;
		$task		= $this->getTask();

		// Determine the name of the primary key for the data.
		if (empty($key)) {
			$key = $table->getKeyName();
		}

		// The urlVar may be different from the primary key to avoid data collisions.
		if (empty($urlVar)) {
			$urlVar = $key;
		}

		$recordId	= JRequest::getInt($urlVar);

		$session	= JFactory::getSession();
		$registry	= $session->get('registry');

		// Populate the row id from the session.
		$data[$key] = $recordId;

		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy') {

			// Reset the ID and then treat the request as for Apply.
			$data[$key]	= 0;
			$task		= 'apply';
		}

		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$form = $model->getForm($data, false);


		// Test if the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false) {
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option='.$this->com.'&view='.$this->view_item.$this->getRedirectAppend($recordId, $key), false));

			return false;
		}

		// Attempt to save the data.
		if (!$model->save($validData)) {

			// Redirect back to the edit screen.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect('index.php?option='.$this->com.'&view='.$this->view_item.$this->getRedirectAppend($recordId, $key));

			return false;
		}

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'edit':
				// Set the record data in the session.
				$recordId = $model->getState($this->view_item.'.id');

				// Redirect to the list screen -> current page
				$this->setRedirect(JRoute::_('index.php?option='.$this->com.'&view='.$this->view_item.$this->getRedirectAppend($recordId, $key), false));
				break;


			default:
				// Redirect to the list screen -> first page
				$this->setRedirect('index.php?option='.$this->com.'&view='.$this->view_list);
				break;
		}

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model, $validData);

		return true;
	}
}