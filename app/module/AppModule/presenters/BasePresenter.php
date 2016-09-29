<?php

namespace App\AppModule\Presenters;

use App\BaseModule\Presenters\BasePresenter as BaseBasePresenter;
use App\Model\Entity\Communication;
use App\Model\Entity\Company;
use App\Model\Entity\Role;
use App\Model\Entity\Sender;
use App\Model\Entity\User;
use App\Model\Facade\CommunicationFacade;
use App\Model\Facade\CompanyFacade;
use Nette\Application\ApplicationException;
use Tracy\Debugger;

abstract class BasePresenter extends BaseBasePresenter
{

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var Sender */
	protected $sender;

	/** @var Company */
	protected $company;

	private $showRightSideBar = false;

	protected function startup()
	{
		parent::startup();
		$this->checkCompleteAccount();
		$this->chooseCompany();
		$this->chooseSender();
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
		if (!$this->user->loggedIn) {
			$this->redirect(':Front:Sign:in');
		}
		if ($this->user->isInRole(Role::CANDIDATE) && !$this->isCompleteAccount() && $this->name !== 'App:CompleteAccount') {
			$this->redirect(':App:CompleteAccount:');
		}
	}

	private function chooseCompany()
	{
		if ($this->getUser()->isInRole(Role::COMPANY)) {
			$companies = $this->companyFacade->findByUser($this->user);
			if ($companies->count()) {
				$this->company = $companies->first();
			}
		}
	}

	private function chooseSender()
	{
		$senders = $this->communicationFacade->findSenders($this->user->identity, $this->company);
		if (count($senders)) {
			$this->sender = current($senders);
		} else {
			$this->sender = $this->communicationFacade->createSender($this->user->identity, $this->company);
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
		$isCompleteAccount = $person->isFilled() && $candidate->isFilled() && $identity->verificated;
		return ($this->getUser()->isInRole(Role::CANDIDATE) && $isCompleteAccount)
		|| $this->getUser()->isInRole(Role::COMPANY)
		|| $this->getUser()->isInRole(Role::ADMIN)
		|| $this->getUser()->isInRole(Role::SUPERADMIN);
	}

	protected function hideRightSidebar()
	{
		$this->showRightSideBar = false;
	}
}
