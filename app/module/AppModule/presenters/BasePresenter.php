<?php

namespace App\AppModule\Presenters;

use App\BaseModule\Presenters\BasePresenter as BaseBasePresenter;
use App\Model\Entity\Communication;
use App\Model\Entity\Role;
use App\Model\Facade\CommunicationFacade;

abstract class BasePresenter extends BaseBasePresenter
{

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	/** @var Communication[] */
	private $userCommunications;

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
		$this->checkUncompleteAccount();
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->isCompleteAccount = !$this->isUncompleteAccount();
		$this->template->allowedLanguages = $this->languageService->allowedLanguages;
		$this->template->communications = $this->getUserCommunications();
		$this->template->unreadMessagesCount = $this->communicationFacade->getUserUnreadCount($this->getUserCommunications(), $this->user->identity);
		$this->template->communicationFacade = $this->communicationFacade;
	}

	/**
	 * If only role is SIGNED, then redirect to complete account
	 */
	private function checkUncompleteAccount()
	{
		if ($this->isUncompleteAccount() && $this->name !== 'App:CompleteAccount') {
			$this->redirect(':App:CompleteAccount:');
		}
	}

	/**
	 * Check if user account is uncomplete
	 * @return bool
	 */
	private function isUncompleteAccount()
	{
		return $this->user->isInRole(Role::SIGNED) && count($this->user->roles) === 1;
	}

}
