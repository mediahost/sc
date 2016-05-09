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

	private $showRightSideBar = true;


	public function getUserCommunications()
	{
		if (!$this->userCommunications) {
			$this->userCommunications = $this->communicationFacade->getUserCommunications($this->user->identity);
		}
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
		if (!$this->isCompleteAccount() && $this->name !== 'App:CompleteAccount') {
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
		return $candidate->isRequiredPersonalFilled() && $candidate->isRequiredOtherFilled()  && $identity->verificated;
	}

	protected function hideRightSidebar()
	{
		$this->showRightSideBar = false;
	}
}
