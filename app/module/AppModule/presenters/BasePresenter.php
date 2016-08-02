<?php

namespace App\AppModule\Presenters;

use App\BaseModule\Presenters\BasePresenter as BaseBasePresenter;
use App\Model\Entity\Communication;
use App\Model\Entity\Role;
use App\Model\Facade\CommunicationFacade;
use Tracy\Debugger;

abstract class BasePresenter extends BaseBasePresenter
{

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	/** @var Communication[] */
	private $userCommunications;

	private $showRightSideBar = false;


	public function getUserCommunications(\App\Model\Entity\User $user=null)
	{
        if(!$user) {
            $user = $this->user->identity;
        }
		$this->userCommunications = $this->communicationFacade->getUserCommunications($user);
		return $this->userCommunications;
	}

	protected function startup()
	{
		parent::startup();
		$this->checkCompleteAccount();
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->isCompleteAccount = $this->isCompleteAccount();
		$this->template->allowedLanguages = $this->languageService->allowedLanguages;
		$this->template->communications = $this->getUserCommunications();
		$this->template->unreadMessagesCount = $this->communicationFacade->getUserUnreadCount($this->getUserCommunications(), $this->user->identity);
		$this->template->communicationFacade = $this->communicationFacade;
		$this->template->showRightSidebar = $this->showRightSideBar;
	}

	private function checkCompleteAccount()
	{
		if (!$this->user->loggedIn) {
			$this->redirect(':Front:Sign:in');
		}
		if ($this->user->isInRole(Role::CANDIDATE) && !$this->isCompleteAccount() && $this->name !== 'App:CompleteAccount') {
			$this->redirect(':App:CompleteAccount:');
		}
	}

	/**
	 * Check if user account is uncomplete
	 * @return bool
	 */
	private function isCompleteAccount()
	{
		$identity = $this->user->identity;
		$candidate = $identity->candidate;
		$isCompleteAccount = $candidate && $candidate->isRequiredPersonalFilled() && $candidate->isRequiredOtherFilled() && $identity->verificated;
		return $isCompleteAccount
            || in_array(Role::COMPANY, $this->getUser()->getRoles())
			|| in_array(Role::ADMIN, $this->getUser()->getRoles())
			|| in_array(Role::SUPERADMIN, $this->getUser()->getRoles());
	}

	protected function hideRightSidebar()
	{
		$this->showRightSideBar = false;
	}
}
