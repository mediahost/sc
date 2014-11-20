<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Model\Entity\User;
use App\Model\Facade;
use App\Model\Storage;
use Nette\Utils\ArrayHash;

/**
 * TODO: Check independency on presenter
 */
class ConnectManagerControl extends BaseControl
{

	const REDIRECT_THIS = 'this#connect-manager';
	const REDIRECT_APP_AUTH = ':App:Profile:settings#set-password';
	const REDIRECT_APP_ACTIVATE = 'activate!';
	const REDIRECT_FACEBOOK_ACTIVATE = 'facebook-open!';
	const REDIRECT_TWITTER_ACTIVATE = 'twitter!';

	/** @var Facade\UserFacade @inject */
	public $userFacade;

	/** @var Storage\UserSettingsStorage @inject */
	public $settingsStorage;

	/** @var User */
	private $user;

	/**
	 * Set user to manage
	 * @param User $user
	 * @return self
	 */
	public function setUser(User $user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * Activation and deactivation.
	 */
	public function render()
	{
		$template = $this->getTemplate();

		$initConnection = ArrayHash::from([
					'name' => NULL,
					'active' => FALSE,
					'link' => NULL,
		]);

		$appConnection = clone $initConnection;
		$appConnection->name = $this->translator->translate('SourceCode');
		$appConnection->active = $this->user->hasSocialConnection(User::SOCIAL_CONNECTION_APP);
		$appConnection->link = $appConnection->active ? 'deactivate!' : 'activate!';

		$fbConnection = clone $initConnection;
		$fbConnection->name = $this->translator->translate('Facebook');
		$fbConnection->active = $this->user->hasSocialConnection(User::SOCIAL_CONNECTION_FACEBOOK);
		$fbConnection->link = $fbConnection->active ? 'deactivate!' : '//dialog-open!';

		$twConnection = clone $initConnection;
		$twConnection->name = $this->translator->translate('Twitter');
		$twConnection->active = $this->user->hasSocialConnection(User::SOCIAL_CONNECTION_TWITTER);
		$twConnection->link = $twConnection->active ? 'deactivate!' : '//authenticate!';


		$sources = [
			$appConnection,
			$fbConnection,
			$twConnection,
		];
		
//		$sources = [
//			self::SOCIAL_AUTH_APP => [
//				'name' => 'SourceCode',
//				'status' => self::STATUS_INACTIVE,
//				'link' => self::REDIRECT_APP_AUTH,
//			],
//			self::SOCIAL_AUTH_FACEBOOK => [
//				'name' => 'Facebook',
//				'status' => self::STATUS_INACTIVE,
//				'link' => 'facebook-open!',
//			],
//			self::SOCIAL_AUTH_TWITTER => [
//				'name' => 'Twitter',
//				'status' => self::STATUS_INACTIVE,
//				'link' => 'twitter!',
//			]
//		];
//		$user = $this->userFacade->find($this->presenter->user->id);
//		$auths = $this->authFacade->findByUser($user);
//
//		$count = count($auths);
//		$lastAuth = $auths[0]->source;
//
//		$template->auths = $auths;
//
//		foreach ($auths as $auth) {
//			switch ($auth->source) {
//				case self::SOCIAL_AUTH_APP:
//					$sources[self::SOCIAL_AUTH_APP]['status'] = 'active';
//					$sources[self::SOCIAL_AUTH_APP]['action'] = 'deactivate';
//					unset($sources[self::SOCIAL_AUTH_APP]['plink']);
//					$sources[self::SOCIAL_AUTH_APP]['link'] = 'deactivate!';
//					$sources[self::SOCIAL_AUTH_APP]['arg'] = $auth->id;
//					break;
//				case self::SOCIAL_AUTH_FACEBOOK:
//					$sources[self::SOCIAL_AUTH_FACEBOOK]['status'] = 'active';
//					$sources[self::SOCIAL_AUTH_FACEBOOK]['action'] = 'deactivate';
//					$sources[self::SOCIAL_AUTH_FACEBOOK]['link'] = 'deactivate!';
//					$sources[self::SOCIAL_AUTH_FACEBOOK]['arg'] = $auth->id;
//					break;
//				case self::SOCIAL_AUTH_TWITTER:
//					$sources[self::SOCIAL_AUTH_TWITTER]['status'] = 'active';
//					$sources[self::SOCIAL_AUTH_TWITTER]['action'] = 'deactivate';
//					$sources[self::SOCIAL_AUTH_TWITTER]['link'] = 'deactivate!';
//					$sources[self::SOCIAL_AUTH_TWITTER]['arg'] = $auth->id;
//					break;
//				default:
//					break;
//			}
//		}
//
//		if ($count < 2) {
//			$sources[$lastAuth]['action'] = NULL;
//		}

		$template->sources = $sources;

		parent::render();
	}

	public function handleActivate($type)
	{
		switch ($type) {
			case self::SOCIAL_AUTH_APP:
				break;
			case self::SOCIAL_AUTH_FACEBOOK:
				break;
			case self::SOCIAL_AUTH_TWITTER:
				break;
		}

		$this->endHandle(self::REDIRECT_THIS);
	}

	public function handleDeactivate($id)
	{
		$auth = $this->authDao->findOneBy(['id' => $id]);

		if ($auth) {
			if (count($this->authFacade->findByUser($auth->user)) > 1) {
				$this->authDao->delete($auth);
				$this->presenter->flashMessage('Connection has been deactivated.');
			} else {
				$this->presenter->flashMessage('Last login method is not possible deactivate.');
			}
		}

		$this->endHandle(self::REDIRECT_THIS);
	}

	private function endHandle($code)
	{
		if ($this->presenter->isAjax()) {
			$this->redrawControl();
		} else {
			$this->redirect($code);
		}
	}

}

interface IConnectManagerControlFactory
{

	/** @return ConnectManagerControl */
	function create();
}
