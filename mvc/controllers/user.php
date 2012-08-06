<?php
CLoader::import('application.controller');

/**
 * User Controller
 */
class UserController extends CController
{
	public function edit()
	{
		$user = CLoader::getUser();
		if ($user->item->id === 0) {
			$this->return->code	= 2;
		}
		else {
			if ($this->input->get('submit')) {
				CLoader::import('ultility.string', 1);

				$user->addValidation('fullname', 'required', 'Please fill in Full name');

				if ($password = $this->input->get('password', '')) {
					$user->item->password = md5($password.$user->item->salt);
				}

				$user->item->fullname = $this->input->get('fullname', '');
				$user->item->params	= new CRegistry();
				$user->item->params->set('website', $this->input->get('website', ''));
				$user->item->params->set('twitter', $this->input->get('twitter', ''));

				if ($user->validateForm()) {
					$user->update();
					$this->return->code	= 2;
					$this->return->url	= 'user?task=edit';
					return null;
				}
			}

			$view = $this->getView('edit');
			$this->output->setVars('content', $user);
			$this->output->setBody('content', $view);
		}
	}

	/**
	 * Method to register a user.
	 *
	 * @return	void
	 */
	public function register()
	{
		$user = $this->getModel();

		if ($this->input->get('submit')) {
			CLoader::import('ultility.string', 1);

			$user->addValidation('email', 'required', 'Please fill in Email');
			$user->addValidation('email', 'email', 'The input for Email should be a valid email value');
			$user->addValidation('email', 'email_exist', 'This Email has already existed');
			$user->addValidation('password', 'required', 'Please fill in Password');
			$user->addValidation('fullname', 'required', 'Please fill in Full name');

			$user->item->email		= $this->input->get('email', '');
			$user->item->password	= $this->input->get('password', '');
			$user->item->fullname	= $this->input->get('fullname', '');
			$user->item->salt		= CString::generateSalt();
			$user->item->params	= new CRegistry();
			$user->item->params->set('website', $this->input->get('website', ''));
			$user->item->params->set('twitter', $this->input->get('twitter', ''));

			if ($user->validateForm()) {
				$user->item->password = md5($user->item->password.$user->item->salt);
				$user->insert();

				$this->return->code	= 2;
				$this->return->url	= 'user?task=login';
				return null;
			}
		}

		$view = $this->getView('register');
		$this->output->setVars('content', $user);
		$this->output->setBody('content', $view);
	}

	/**
	 * Method to log in a user.
	 *
	 * @return	void
	 */
	public function login()
	{
		$user = $this->getModel();
		if ($this->input->get('submit')) {
			$user->item->email		= $this->input->get('email');
			$user->item->password	= $this->input->get('password');
			$return			= $this->input->get('return');

			if ($user->login()) {
				$ss = CLoader::getSession();
				$ss->set('user_id', $user->item->id);

				$this->return->code	= 2;
				$this->return->url	= $return;
				return null;
			}
			else {
				$user->errors = array('Email & password are not matched');
			}

		}
		
		$view = $this->getView('login');
		$this->output->setVars('content', $user);
		$this->output->setBody('content', $view);
	}

	/**
	 * Method to log out a user.
	 *
	 * @return	void
	 */
	public function logout()
	{
		$user = CLoader::getUser()->getItem();

		if ($user->id) {
			$ss = CLoader::getSession();
			$ss->set('user_id', null);
		}

		$this->return->code	= 2;
		$this->return->url	= '';
	}
}
?>