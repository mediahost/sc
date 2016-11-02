<?php

namespace App\AppModule\Presenters;

use App\Components\Company\CompanySelector;
use App\Components\Company\ICompanySelectorFactory;
use App\Components\Grids\Job\IJobsGridFactory;
use App\Components\Grids\Job\JobsGrid;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
use App\Model\Entity\Role;
use App\Model\Facade\CandidateFacade;
use App\Model\Facade\JobFacade;
use App\Model\Repository\CompanyRepository;
use App\Model\Repository\JobRepository;
use Kdyby\Doctrine\EntityDao;
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

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var CompanyRepository */
	private $companyRepo;

	/** @var JobRepository */
	private $jobRepo;

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
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$message = $this->translator->translate('Not implemented yet');
		$this->flashMessage($message, 'warning');
		$this->redirect('Dashboard:');
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

	// </editor-fold>
}
