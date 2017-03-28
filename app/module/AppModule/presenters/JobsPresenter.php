<?php

namespace App\AppModule\Presenters;

use App\Components\Company\CompanySelector;
use App\Components\Company\ICompanySelectorFactory;
use App\Components\Grids\Job\IJobsGridFactory;
use App\Components\Grids\Job\IJobsListFactory;
use App\Components\Grids\Job\JobsGrid;
use App\Components\Grids\Job\JobsList;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
use App\Model\Entity\Role;
use App\Model\Facade\CandidateFacade;
use App\Model\Facade\JobFacade;
use App\Model\Repository\CompanyRepository;
use App\Model\Repository\JobRepository;
use Kdyby\Doctrine\EntityManager;

class JobsPresenter extends BasePresenter
{

	// <editor-fold desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var JobFacade @inject */
	public $jobFacade;

	/** @var CandidateFacade @inject */
	public $candidateFacade;

	/** @var ICompanySelectorFactory @inject */
	public $iCompanySelectorFactory;

	/** @var IJobsGridFactory @inject */
	public $iJobsGridFactory;

	/** @var IJobsListFactory @inject */
	public $iJobsListFactory;

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var CompanyRepository */
	private $companyRepo;

	/** @var JobRepository */
	private $jobRepo;

	/** @var Company */
	private $viewedCompany;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->companyRepo = $this->em->getRepository(Company::getClassName());
		$this->jobRepo = $this->em->getRepository(Job::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		if ($this->user->isAllowed('jobs', 'showAll')) {
			$this->redirect('showAll');
		} else {
			$this->redirect('myList');
		}
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('showAll')
	 */
	public function actionShowAll()
	{
		if ($this->company) {
			$this['jobsGrid']->setCompany($this->company);
		}
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('myList')
	 */
	public function actionMyList()
	{
		if (!$this->user->isInRole(Role::CANDIDATE)) {
			$this->flashMessage($this->translator->translate('This section is only for candidate'));
			$this->redirect('Dashboard:');
		}
		$candidate = $this->user->getIdentity()->candidate;
		$this->template->candidate = $candidate;
		$this->template->jobs = $this->jobRepo->findAll();
		$this->template->invitations = $this->candidateFacade->findApprovedJobs($candidate, FALSE);
		$this->template->applied = $this->candidateFacade->findAppliedJobs($candidate, FALSE);
		$this->template->matches = $this->candidateFacade->findMatchedJobs($candidate);
		$this->template->candidateFacade = $this->candidateFacade;
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('myList')
	 */
	public function actionMyList2()
	{
		if (!$this->user->isInRole(Role::CANDIDATE)) {
			$this->flashMessage($this->translator->translate('This section is only for candidate'));
			$this->redirect('Dashboard:');
		}

		$candidate = $this->user->getIdentity()->candidate;
		$this['allJobs']
			->setCandidate($candidate);
		$this['approvedJobs']
			->setCandidate($candidate);
		$this['appliedJobs']
			->setCandidate($candidate);
	}

	public function renderMyList2()
	{
		$this->template->showJobSections = !$this['allJobs']->isFiltered();
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('filter')
	 */
	public function actionFilter()
	{

	}

	public function renderFilter()
	{
		$this->template->showJobSections = !$this['allJobs']->isFiltered();
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('company')
	 */
	public function actionCompany($id)
	{
		if ($id) {
			$this->viewedCompany = $this->companyRepo->find($id);
			if ($this->viewedCompany) {
				$this['jobsGrid']->setCompany($this->viewedCompany);
			}
		}
		if (!$this->viewedCompany) {
			$this->flashMessage($this->translator->translate('No such company'), 'warning');
			$this->redirect('default');
		}
	}

	public function renderCompany()
	{
		$this->template->company = $this->viewedCompany;
	}

	// </editor-fold>
	// <editor-fold desc="controls">

	/** @return CompanySelector */
	public function createComponentCompanySelector()
	{
		$control = $this->iCompanySelectorFactory->create();
		$control->onAfterSelect = function (Company $company) {
			$this->redirect('Job:add', $company->id);
		};
		return $control;
	}

	/** @return JobsGrid */
	public function createComponentJobsGrid()
	{
		$control = $this->iJobsGridFactory->create();
		return $control;
	}

	/** @return JobsList */
	public function createComponentAllJobs()
	{
		$control = $this->iJobsListFactory->create()
			->setShowFilter()
			->setShowPaginator()
			->setNoMatchText('There are no jobs matching your criteria.');
		return $control;
	}

	/** @return JobsList */
	public function createComponentApprovedJobs()
	{
		$control = $this->iJobsListFactory->create()
			->setOnlyApproved()
			->setNoMatchText('We are searching for interesting opportunities for you.');
		return $control;
	}

	/** @return JobsList */
	public function createComponentAppliedJobs()
	{
		$control = $this->iJobsListFactory->create()
			->setOnlyApplied()
			->setOnlyMatched()
			->setShowRejected(TRUE)
			->setNoMatchText('You have not applied for any jobs');
		return $control;
	}

	// </editor-fold>
}
