<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Model\Facade;
use App\Model\Storage;
use App\Model\Storage\RegistrationStorage;

/**
 * TODO: Check independency on presenter
 */
class ConnectControl extends BaseControl
{

	/** @var RegistrationStorage @inject */
	public $storage;

	/** @var Facade\UserFacade @inject */
	public $userFacade;

	/** @var Storage\UserSettingsStorage @inject */
	public $settingsStorage;

	/**
	 * Activation and deactivation.
	 */
	public function renderConnect()
	{
		$template = $this->template;

		$sources = [
			RegistrationStorage::SOURCE_APP => [
				'name' => 'SourceCode',
				'status' => 'deactivated',
				'action' => 'activate',
				'plink' => ':App:Profile:settings#set-password'
			],
			RegistrationStorage::SOURCE_FACEBOOK => [
				'name' => 'Facebook',
				'status' => 'deactivated',
				'action' => 'activate',
				'link' => 'facebook-open!'
			],
			RegistrationStorage::SOURCE_TWITTER => [
				'name' => 'Twitter',
				'status' => 'deactivated',
				'action' => 'activate',
				'link' => 'twitter!'
			]
		];

		$user = $this->userFacade->find($this->presenter->user->id);
		$auths = $this->authFacade->findByUser($user);

		$count = count($auths);
		$lastAuth = $auths[0]->source;

		$template->auths = $auths;

		foreach ($auths as $auth) {
			switch ($auth->source) {
				case RegistrationStorage::SOURCE_APP:
					$sources[RegistrationStorage::SOURCE_APP]['status'] = 'active';
					$sources[RegistrationStorage::SOURCE_APP]['action'] = 'deactivate';
					unset($sources[RegistrationStorage::SOURCE_APP]['plink']);
					$sources[RegistrationStorage::SOURCE_APP]['link'] = 'deactivate!';
					$sources[RegistrationStorage::SOURCE_APP]['arg'] = $auth->id;
					break;
				case RegistrationStorage::SOURCE_FACEBOOK:
					$sources[RegistrationStorage::SOURCE_FACEBOOK]['status'] = 'active';
					$sources[RegistrationStorage::SOURCE_FACEBOOK]['action'] = 'deactivate';
					$sources[RegistrationStorage::SOURCE_FACEBOOK]['link'] = 'deactivate!';
					$sources[RegistrationStorage::SOURCE_FACEBOOK]['arg'] = $auth->id;
					break;
				case RegistrationStorage::SOURCE_TWITTER:
					$sources[RegistrationStorage::SOURCE_TWITTER]['status'] = 'active';
					$sources[RegistrationStorage::SOURCE_TWITTER]['action'] = 'deactivate';
					$sources[RegistrationStorage::SOURCE_TWITTER]['link'] = 'deactivate!';
					$sources[RegistrationStorage::SOURCE_TWITTER]['arg'] = $auth->id;
					break;
				default:
					break;
			}
		}

		if ($count < 2) {
			$sources[$lastAuth]['action'] = NULL;
		}

		$template->sources = $sources;

		$template->setFile(__DIR__ . '/connect.latte');
		$template->render();
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

		if ($this->presenter->isAjax()) {
			$this->redrawControl();
		} else {
			$this->redirect('this#connect-manager');
		}
	}

}
