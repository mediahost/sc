<?php

namespace App\AppModule\Presenters;

use App\Components\Grids\Job\IJobsGridFactory;
use App\Components\Job\IJobDataViewFactory;
use App\Components\Grids\Job\JobsGrid;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;

/**
 * Jobs presenter.
 */
class JobsPresenter extends BasePresenter
{
	// <editor-fold desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var IJobsGridFactory @inject */
	public $iJobsGridFactory;

	/** @var IJobDataViewFactory @inject */
	public $iJobDataViewFactory;
	
	/** @var \App\Model\Facade\JobFacade @inject */
	public $jobFacade;
	
	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var EntityDao */
	private $companyDao;

	/** @var EntityDao */
	private $jobDao;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->companyDao = $this->em->getDao(Company::getClassName());
		$this->jobDao = $this->em->getDao(Job::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('default')
	 */
	public function actionDefault($id)
	{
		$company = $this->companyDao->find($id);
		if ($company) {
			$jobs = $this->jobFacade->findByCompany($company);
			$this['jobsDataView']->setJobs($jobs);
			$this['jobsDataView']->setCompany($company);
			$this->template->company = $company;
		} else {
			$this->flashMessage('Finded company isn\'t exists.', 'danger');
			$this->redirect('Dashboard:');
		}
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$this->flashMessage('Not implemented yet', 'warning');
		$this->redirect('Dashboard:');
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('showAll')
	 */
	public function actionShowAll()
	{
		if(in_array(\App\Model\Entity\Role::COMPANY, $this->getUser()->getRoles())) {
			$jobs = $this->jobFacade->findByUser($this->getUser());
		} else {
			$jobs = $this->jobFacade->findAll();
		}
		$this['jobsDataView']->setJobs($jobs);
	}

	// </editor-fold>
	// <editor-fold desc="grids">

	/** @return JobsGrid */
	public function createComponentJobsGrid()
	{
		$control = $this->iJobsGridFactory->create();
		return $control;
	}
	
	public function createComponentJobsDataView()
	{
		$control = $this->iJobDataViewFactory->create();
		return $control;
	}

	// </editor-fold>
}
