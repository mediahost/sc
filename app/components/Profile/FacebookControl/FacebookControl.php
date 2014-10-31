<?php

namespace App\Components\Profile;

use App\Components\BaseControl;
use App\Model\Entity;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Kdyby\Facebook\Dialog\LoginDialog;
use Kdyby\Facebook\Facebook;
use Kdyby\Facebook\FacebookApiException;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

class FacebookControl extends BaseControl
{

	public $onSuccess = [];

	/** @var Facebook @inject */
	public $facebook;

	/** @var SignUpStorage @inject */
	public $session;
	
	/** @var UserFacade @inject */
	public $userFacade;

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
				Debugger::barDump($me);

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
	 * @return Entity\User
	 */
	protected function createUser(ArrayHash $me)
	{
		$user = new Entity\User();
		$user->name = $me->name;

		if (isset($me->email)) {
			$user->mail = $me->email;
			$this->session->verification = TRUE;
		} else {
			$this->session->verification = FALSE;
		}

		$fb = new Entity\Facebook();
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