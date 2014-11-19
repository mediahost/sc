<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Model\Entity;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Kdyby\Doctrine\EntityManager;
use Netrium\Addons\Twitter\AuthenticationException as TwitterException;
use Netrium\Addons\Twitter\Authenticator as Twitter;
use Tracy\Debugger;

class TwitterControl extends BaseControl
{

	public $onSuccess = [];

	/** @var SignUpStorage @inject */
	public $session;

	/** @var EntityManager @inject */
	public $em;

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

			$user = $this->userFacade->findByTwitterId($data['user']->id_str);
			if ($user) {
				$this->loadTwitterEntity($user->twitter, $data);
				$this->em->getDao(Entity\Twitter::getClassName())->save($user->twitter);
			} else {
				$user = $this->createUser($data);
			}

			$this->onSuccess($this, $user);
		} catch (TwitterException $e) {
			Debugger::log($e->getMessage(), 'twitter');
			$this->presenter->flashMessage('We are sorry, twitter authentication failed hard.');
		}
	}

	/**
	 * @param array $data
	 * @return Entity\User
	 */
	protected function createUser(array $data)
	{
		$userData = $data['user'];
		$user = new Entity\User();
		$user->requiredRole = $this->roleFacade->findByName($this->session->getRole(TRUE));

		$twitter = new Entity\Twitter();
		$twitter->id = $userData->id_str;
		$this->loadTwitterEntity($twitter, $data);
		$user->twitter = $twitter;

		$this->session->verification = FALSE;
		return $user;
	}

	/**
	 * Load data to TW entity
	 * @param Entity\Twitter $twitter
	 * @param array $data
	 */
	protected function loadTwitterEntity(Entity\Twitter &$twitter, array $data)
	{
		if (array_key_exists('user', $data)) {
			$userData = $data['user'];
			if (isset($userData->name)) {
				$twitter->name = $userData->name;
			}
			if (isset($userData->screen_name)) {
				$twitter->screenName = $userData->screen_name;
			}
			if (isset($userData->location)) {
				$twitter->location = $userData->location;
			}
			if (isset($userData->description)) {
				$twitter->description = $userData->description;
			}
			if (isset($userData->url)) {
				$twitter->url = $userData->url;
			}
			if (isset($userData->statuses_count)) {
				$twitter->statusesCount = $userData->statuses_count;
			}
			if (isset($userData->lang)) {
				$twitter->lang = $userData->lang;
			}
		}
		$twitter->accessToken = $data['accessToken']['key'];
	}

}

interface ITwitterControlFactory
{

	/** @return TwitterControl */
	function create();
}
