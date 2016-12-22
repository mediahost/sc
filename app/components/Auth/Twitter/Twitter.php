<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Model\Entity;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Netrium\Addons\Twitter\AuthenticationException;
use Netrium\Addons\Twitter\Authenticator;
use Tracy\Debugger;

class Twitter extends BaseControl
{

	/** @var array */
	public $onSuccess = [];

	/** @var array */
	public $onConnect = [];

	/** @var bool */
	private $onlyConnect = FALSE;

	/** @var Authenticator @inject */
	public $twitter;

	/** @var SignUpStorage @inject */
	public $session;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/**
	 * @var bool
	 * @persistent
	 */
	public $remember = FALSE;

	/**
	 * @var int
	 * @persistent
	 */
	public $jobApplyId;

	/**
	 * @var string
	 * @persistent
	 */
	public $redirectUrl;

	public function handleAuthenticate()
	{
		try {
			$data = $this->twitter->tryAuthenticate();

			if ($this->onlyConnect) {
				$tw = new Entity\Twitter($data['user']->id_str);
				$this->loadTwitterEntity($tw, $data);
				$this->onConnect($tw);
			} else {
				$user = $this->userFacade->findByTwitterId($data['user']->id_str);
				if ($user) {
					$this->loadTwitterEntity($user->twitter, $data);
					$this->em->getDao(Entity\Twitter::getClassName())->save($user->twitter);
				} else {
					$user = $this->createUser($data);
				}

				$this->onSuccess($this, $user, $this->remember, $this->redirectUrl, $this->jobApplyId);
			}
		} catch (AuthenticationException $e) {
			Debugger::log($e->getMessage(), 'twitter');
			$message = $this->translator->translate('We are sorry, %method% authentication failed hard.', ['method' => 'Twitter']);
			$this->presenter->flashMessage($message);
		}
	}

	public function render()
	{
		$template = $this->getTemplate();
		$template->link = $this->getLink();
		parent::render();
	}

	// <editor-fold desc="load & create">

	/**
	 * @param array $data
	 * @return Entity\User
	 */
	protected function createUser(array $data)
	{
		$user = new Entity\User();

		$userData = $data['user'];
		$twitter = new Entity\Twitter($userData->id_str);
		$this->loadTwitterEntity($twitter, $data);
		$user->twitter = $twitter;

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

	// </editor-fold>
	// <editor-fold desc="setters">

	/**
	 * Fire onConnect event besides onSuccess
	 * @param bool $onlyConnect
	 * @return self
	 */
	public function setConnect($onlyConnect = TRUE)
	{
		$this->onlyConnect = $onlyConnect;
		return $this;
	}

	// </editor-fold>
	// <editor-fold desc="getters">

	/**
	 * return link to open dialog
	 * @return type
	 */
	public function getLink()
	{
		return $this->link('//authenticate!');
	}

	// </editor-fold>
}

interface ITwitterFactory
{

	/** @return Twitter */
	function create();
}
