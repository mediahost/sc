<?php

namespace App\Components\Auth;

use App\Components\Auth;
use App\Components\BaseControl;
use App\Model\Entity;
use Nette\Utils\ArrayHash;

class ConnectManager extends BaseControl
{

	const APP = 'App login';
	const FACEBOOK = 'Facebook';
	const TWITTER = 'Twitter';
	const LINKEDIN = 'Linked In';

	// <editor-fold desc="events">

	/** @var array */
	public $onConnect = [];

	/** @var array */
	public $onDisconnect = [];

	/** @var array */
	public $onLastConnection = [];

	/** @var array */
	public $onInvalidType = [];

	/** @var array */
	public $onUsingConnection = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var IFacebookFactory @inject */
	public $iFacebookFactory;

	/** @var ITwitterFactory @inject */
	public $iTwitterFactory;

	/** @var ILinkedinFactory @inject */
	public $iLinkedinFactory;

	// </editor-fold>

	/** @var Entity\User */
	private $user;

	/** @var string */
	private $redirectAppActivate;

	/**
	 * Set user to manage
	 * @param Entity\User $user
	 * @return self
	 */
	public function setUser(Entity\User $user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * Set destination to redirect to activate app account
	 * @param string $link put result of $this->link()
	 * @param bool $relative if TRUE then link will be transformed to link
	 * @return self
	 */
	public function setAppActivateRedirect($link, $relative = FALSE)
	{
		if ($relative) {
			$link = $this->link($link);
		}
		$this->redirectAppActivate = $link;
		return $this;
	}

	/**
	 * Activation and deactivation.
	 */
	public function render()
	{
		$initConnection = ArrayHash::from([
			'name' => NULL,
			'active' => FALSE,
			'link' => '#',
		]);

		$appConnection = clone $initConnection;
		$appConnection->name = $this->translator->translate('SourceCode');
		$appConnection->active = $this->user->hasSocialConnection(Entity\User::SOCIAL_CONNECTION_APP);
		$appConnection->link = $appConnection->active ?
			$this->link('deactivate!', Entity\User::SOCIAL_CONNECTION_APP) :
			$this->redirectAppActivate ? $this->redirectAppActivate : '#';

		$fbConnection = clone $initConnection;
		$fbConnection->name = $this->translator->translate('Facebook');
		$fbConnection->active = $this->user->hasSocialConnection(Entity\User::SOCIAL_CONNECTION_FACEBOOK);
		$fbConnection->link = $fbConnection->active ?
			$this->link('deactivate!', Entity\User::SOCIAL_CONNECTION_FACEBOOK) :
			$this['facebook']->getLink();

		$twConnection = clone $initConnection;
		$twConnection->name = $this->translator->translate('Twitter');
		$twConnection->active = $this->user->hasSocialConnection(Entity\User::SOCIAL_CONNECTION_TWITTER);
		$twConnection->link = $twConnection->active ?
			$this->link('deactivate!', Entity\User::SOCIAL_CONNECTION_TWITTER) :
			$this['twitter']->getLink();

		$liConnection = clone $initConnection;
		$liConnection->name = $this->translator->translate('Linked In');
		$liConnection->active = $this->user->hasSocialConnection(Entity\User::SOCIAL_CONNECTION_LINKEDIN);
//		$liConnection->link = $liConnection->active ?
//			$this->link('deactivate!', User::SOCIAL_CONNECTION_LINKEDIN) :
//			$this['linkedin']->getLink();
		$liConnection->link = $this['linkedin']->getLink();

		$sources = [
			$appConnection,
			$fbConnection,
			$twConnection,
			$liConnection,
		];

		$template = $this->getTemplate();
		$template->sources = $sources;
		$template->canDisconnect = $this->user->connectionCount > 1;
		parent::render();
	}

	public function handleDeactivate($type)
	{
		if ($this->user->connectionCount <= 1) {
			$this->onLastConnection();
			$this->redrawControl();
		}

		$userRepo = $this->em->getRepository(Entity\User::getClassName());
		$user = $userRepo->find($this->user->id);

		$disconected = NULL;
		switch ($type) {
			case Entity\User::SOCIAL_CONNECTION_APP:
				$disconected = self::APP;
				$user->clearHash();
				break;
			case Entity\User::SOCIAL_CONNECTION_FACEBOOK:
				$disconected = self::FACEBOOK;
				$user->clearFacebook();
				break;
			case Entity\User::SOCIAL_CONNECTION_TWITTER:
				$disconected = self::TWITTER;
				$user->clearTwitter();
				break;
			case Entity\User::SOCIAL_CONNECTION_LINKEDIN:
				$disconected = self::LINKEDIN;
				$user->clearLinkedin();
				break;
		}
		if ($disconected) {
			$savedUser = $userRepo->save($user);
			$this->onDisconnect($savedUser, $disconected);
		} else {
			$this->onInvalidType($type);
		}
		$this->redrawControl();
	}

	// <editor-fold desc="controls">

	/** @return Auth\Facebook */
	protected function createComponentFacebook()
	{
		$control = $this->iFacebookFactory->create();
		$control->setConnect();
		$control->onConnect[] = function (Entity\Facebook $fb) {
			$fbDao = $this->em->getDao(Entity\Facebook::getClassName());
			if ($fbDao->find($fb->id)) {
				$this->onUsingConnection(self::FACEBOOK);
				return;
			}
			$userDao = $this->em->getDao(Entity\User::getClassName());
			$user = $userDao->find($this->user->id);
			if (!$user->hasSocialConnection(Entity\User::SOCIAL_CONNECTION_FACEBOOK)) {
				$user->facebook = $fb;
				$userDao->save($user);
			}
			$this->onConnect(self::FACEBOOK);
		};
		return $control;
	}

	/** @return Auth\Twitter */
	protected function createComponentTwitter()
	{
		$control = $this->iTwitterFactory->create();
		$control->setConnect();
		$control->onConnect[] = function (Entity\Twitter $tw) {
			$twDao = $this->em->getDao(Entity\Twitter::getClassName());
			if ($twDao->find($tw->id)) {
				$this->onUsingConnection(self::TWITTER);
				return;
			}
			$userDao = $this->em->getDao(Entity\User::getClassName());
			$user = $userDao->find($this->user->id);
			if (!$user->hasSocialConnection(Entity\User::SOCIAL_CONNECTION_TWITTER)) {
				$user->twitter = $tw;
				$userDao->save($user);
			}
			$this->onConnect(self::TWITTER);
		};
		return $control;
	}

	/** @return Auth\Linkedin */
	protected function createComponentLinkedin()
	{
		$control = $this->iLinkedinFactory->create();
		$control->setConnect();
		$control->onConnect[] = function (Entity\Linkedin $li) {
			$liDao = $this->em->getDao(Entity\Linkedin::getClassName());
			if ($liDao->find($li->id)) {
				$this->onUsingConnection(self::LINKEDIN);
				return;
			}
			$userDao = $this->em->getDao(Entity\User::getClassName());
			$user = $userDao->find($this->user->id);
			if (!$user->hasSocialConnection(Entity\User::SOCIAL_CONNECTION_LINKEDIN)) {
				$user->linkedin = $li;
				$userDao->save($user);
			}
			$this->onConnect(self::LINKEDIN);
		};
		return $control;
	}

	// </editor-fold>
}

interface IConnectManagerFactory
{

	/** @return ConnectManager */
	function create();
}
