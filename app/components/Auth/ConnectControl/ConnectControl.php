<?php

namespace App\Components\Auth;

use App\Components\BaseControl;
use App\Model\Entity\Facebook;
use App\Model\Entity\Twitter;
use App\Model\Entity\User;
use App\Model\Storage;
use Kdyby\Doctrine\EntityManager;
use Nette\Utils\ArrayHash;

/**
 * TODO: Check independency on presenter
 */
class ConnectManagerControl extends BaseControl
{

	const REDIRECT_THIS = 'this#connect-manager';
	const REDIRECT_APP_ACTIVATE = 'this#set-password';

	/** @var EntityManager @inject */
	public $em;

	/** @var IFacebookControlFactory @inject */
	public $iFacebookControlFactory;

	/** @var ITwitterControlFactory @inject */
	public $iTwitterControlFactory;

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
		$initConnection = ArrayHash::from([
					'name' => NULL,
					'active' => FALSE,
					'link' => '#',
		]);

		$appConnection = clone $initConnection;
		$appConnection->name = $this->translator->translate('SourceCode');
		$appConnection->active = $this->user->hasSocialConnection(User::SOCIAL_CONNECTION_APP);
		$appConnection->link = $appConnection->active ?
				$this->link('deactivate!', User::SOCIAL_CONNECTION_APP) :
				$this->link(self::REDIRECT_APP_ACTIVATE);

		$fbConnection = clone $initConnection;
		$fbConnection->name = $this->translator->translate('Facebook');
		$fbConnection->active = $this->user->hasSocialConnection(User::SOCIAL_CONNECTION_FACEBOOK);
		$fbConnection->link = $fbConnection->active ?
				$this->link('deactivate!', User::SOCIAL_CONNECTION_FACEBOOK) :
				$this['facebook']->getLink();

		$twConnection = clone $initConnection;
		$twConnection->name = $this->translator->translate('Twitter');
		$twConnection->active = $this->user->hasSocialConnection(User::SOCIAL_CONNECTION_TWITTER);
		$twConnection->link = $twConnection->active ?
				$this->link('deactivate!', User::SOCIAL_CONNECTION_TWITTER) :
				$this['twitter']->getLink();

		$sources = [
			$appConnection,
			$fbConnection,
			$twConnection,
		];

		$template = $this->getTemplate();
		$template->sources = $sources;
		$template->canDisconnect = $this->user->connectionCount > 1;
		parent::render();
	}

	public function handleDeactivate($type)
	{
		if ($this->user->connectionCount <= 1) {
			$this->presenter->flashMessage('Last login method is not possible deactivate.', 'warning');
			$this->endHandle(self::REDIRECT_THIS);
		}
		
		$userDao = $this->em->getDao(User::getClassName());
		$user = $userDao->find($this->user->id);
		
		$disconected = NULL;
		switch ($type) {
			case User::SOCIAL_CONNECTION_APP:
				$disconected = 'SourceCode login';
				$user->clearHash();
				break;
			case User::SOCIAL_CONNECTION_FACEBOOK:
				$disconected = 'Facebook';
				$user->clearFacebook();
				break;
			case User::SOCIAL_CONNECTION_TWITTER:
				$disconected = 'Twitter';
				$user->clearTwitter();
				break;
		}
		if ($disconected) {
			$userDao->save($user);
			$this->presenter->flashMessage($disconected .' was disconnect', 'success');
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

	// <editor-fold defaultstate="collapsed" desc="controls">

	/** @return FacebookControl */
	protected function createComponentFacebook()
	{
		$control = $this->iFacebookControlFactory->create();
		$control->setConnect();
		$control->onConnect[] = function (Facebook $fb) {
			$userDao = $this->em->getDao(User::getClassName());
			$user = $userDao->find($this->user->id);
			if (!$user->hasSocialConnection(User::SOCIAL_CONNECTION_FACEBOOK)) {
				$user->facebook = $fb;
				$userDao->save($user);
			}
			$this->presenter->redirect(self::REDIRECT_THIS);
		};
		return $control;
	}

	/** @return TwitterControl */
	protected function createComponentTwitter()
	{
		$control = $this->iTwitterControlFactory->create();
		$control->setConnect();
		$control->onConnect[] = function (Twitter $fb) {
			$userDao = $this->em->getDao(User::getClassName());
			$user = $userDao->find($this->user->id);
			if (!$user->hasSocialConnection(User::SOCIAL_CONNECTION_TWITTER)) {
				$user->twitter = $fb;
				$userDao->save($user);
			}
			$this->presenter->redirect(self::REDIRECT_THIS);
		};
		return $control;
	}

	// </editor-fold>
}

interface IConnectManagerControlFactory
{

	/** @return ConnectManagerControl */
	function create();
}
