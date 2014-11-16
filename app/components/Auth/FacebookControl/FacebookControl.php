<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Kdyby\Facebook\Dialog\LoginDialog;
use Kdyby\Facebook\Facebook;
use Kdyby\Facebook\FacebookApiException;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

class FacebookControl extends BaseControl
{

	/** @var array */
	public $onSuccess = [];

	/** @var Facebook @inject */
	public $facebook;

	/** @var SignUpStorage @inject */
	public $session;
	
	/** @var UserFacade @inject */
	public $userFacade;
	
	/** @var RoleFacade @inject */
	public $roleFacade;

	protected function createComponentDialog()
	{
		$dialog = $this->facebook->createDialog('login');

		/** @var LoginDialog $dialog */
		$dialog->onResponse[] = function (LoginDialog $dialog) {
			$fb = $dialog->getFacebook();

			if (!$fb->getUser()) {
				$this->presenter->flashMessage('We are sorry, facebook authentication failed.');
				return;
			}

			try {
				$me = $fb->api('/me');

				if (!$user = $this->userFacade->findByFacebookId($fb->getUser())) {
					$user = $this->createUser($me);
				}

				$user->facebook->accessToken = $fb->getAccessToken();

				$this->onSuccess($this, $user);
			} catch (FacebookApiException $e) {
				Debugger::log($e->getMessage(), 'facebook');
				$this->presenter->flashMessage('We are sorry, facebook authentication failed hard.');
			}
		};

		return $dialog;
	}

	/**
	 * @param ArrayHash $me
	 * @return User
	 */
	protected function createUser(ArrayHash $me)
	{
		$user = new User();
		$user->name = $me->name;

		if (isset($me->email)) {
			$user->mail = $me->email;
			$this->session->verification = TRUE;
		} else {
			$this->session->verification = FALSE;
		}
		
		$role = $this->roleFacade->findByName($this->session->role);
		$user->addRole($role);

		$fb = new Facebook();
		$fb->id = $me->id;

		$user->facebook = $fb;
		return $user;
	}

}

interface IFacebookControlFactory
{

	/** @return FacebookControl */
	function create();
}
