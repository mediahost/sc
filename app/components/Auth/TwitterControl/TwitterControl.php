<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Netrium\Addons\Twitter\AuthenticationException as TwitterException;
use Netrium\Addons\Twitter\Authenticator as Twitter;
use stdClass;
use Tracy\Debugger;

class TwitterControl extends BaseControl
{

	public $onSuccess = [];

	/** @var SignUpStorage @inject */
	public $session;

	/** @var Twitter @inject */
	public $twitter;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	public function handleAuthenticate()
	{
		try {
			$data = $this->twitter->tryAuthenticate();

			if (!$user = $this->userFacade->findByTwitterId($data['user']->id_str)) {
				$user = $this->createUser($data['user']);
			}

			$user->twitter->accessToken = $data['accessToken']['key'];

			$this->onSuccess($this, $user);
		} catch (TwitterException $e) {
			Debugger::log($e->getMessage(), 'twitter');
			$this->presenter->flashMessage('We are sorry, twitter authentication failed hard.');
		}
	}

	/**
	 * @param stdClass $data
	 * @return User
	 */
	protected function createUser(stdClass $data)
	{
		$user = new User();
		$user->setName($data->name);

		$role = $this->roleFacade->findByName($this->session->role);
		$user->addRole($role);

		$twitter = new Twitter();
		$twitter->id = $data->id_str;

		$user->twitter = $twitter;
		$this->session->verification = FALSE;
		return $user;
	}

}

interface ITwitterControlFactory
{

	/** @return TwitterControl */
	function create();
}
