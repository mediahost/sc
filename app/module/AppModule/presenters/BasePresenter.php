<?php

namespace App\AppModule\Presenters;

use App\BaseModule\Presenters\BasePresenter as BaseBasePresenter;
use App\Extensions\Candidates\ICandidatesListFactory;
use App\Model\Entity\Company;
use App\Model\Entity\Role;
use App\Model\Entity\Sender;
use App\Model\Entity\User;
use App\Model\Facade\CommunicationFacade;
use App\Model\Facade\CompanyFacade;

/**
 * @secured
 */
abstract class BasePresenter extends BaseBasePresenter
{

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var ICandidatesListFactory @inject */
	public $iCandidatesListFactory;

	/** @var Sender */
	protected $sender;

	/** @var Company */
	protected $company;

	private $showRightSideBar = false;

	protected function startup()
	{
		parent::startup();
		$this->chooseCompany();
		$this->chooseSender();
		$this->checkCompleteAccount();
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->isCompleteAccount = $this->isCompleteAccount();
		$this->template->showRightSidebar = $this->showRightSideBar;

		$this->template->sender = $this->sender;
		$this->template->communications = $this->sender->communications;
		$this->template->unreadMessagesCount = $this->sender->unreadCount;
	}

	private function checkCompleteAccount()
	{
		$isInRoles = $this->user->isInRole(Role::CANDIDATE) || $this->user->isInRole(Role::COMPANY);
		if ($isInRoles && !$this->isCompleteAccount() && $this->name !== 'App:CompleteAccount') {
			$this->redirect(':App:CompleteAccount:');
		}
	}

	private function chooseCompany()
	{
		if ($this->user->loggedIn && $this->user->isInRole(Role::COMPANY)) {
			$companies = $this->companyFacade->findByUser($this->user);
			if ($companies->count()) {
				$this->company = $companies->first();
			}
		}
	}

	private function chooseSender()
	{
		if ($this->user->loggedIn) {
			$senders = $this->communicationFacade->findSenders($this->user->identity, $this->company);
			if (count($senders)) {
				$this->sender = current($senders);
			} else {
				$this->sender = $this->communicationFacade->createSender($this->user->identity, $this->company);
			}
		}
	}

	/**
	 * Check if user account is complete
	 * @return bool
	 */
	private function isCompleteAccount()
	{
		/** @var User $identity */
		$identity = $this->user->identity;
		$person = $identity->getPerson();
		$candidate = $person->getCandidate();
		$isCompleteCandidateAccount = $person->isFilled() && $candidate->isFilled() && $identity->verificated;
		$isCompleteCompanyAccount = $this->company && $identity->verificated;
		return ($this->getUser()->isInRole(Role::CANDIDATE) && $isCompleteCandidateAccount)
		|| ($this->getUser()->isInRole(Role::COMPANY) && $isCompleteCompanyAccount)
		|| $this->getUser()->isInRole(Role::ADMIN)
		|| $this->getUser()->isInRole(Role::SUPERADMIN);
	}

	protected function hideRightSidebar()
	{
		$this->showRightSideBar = false;
	}

	public function createComponentCandidatesList()
	{
		$list = $this->iCandidatesListFactory->create();
		$list->setTranslator($this->translator)
			->setItemsPerPage($this->settings->pageConfig->itemsPerRow, $this->settings->pageConfig->rowsPerPage)
			->setAjax();

		return $list;
	}

}
