<?php

namespace App\AppModule\Presenters;

use App\Components\Grids\Job\IJobsGridFactory;
use App\Components\Grids\Job\JobsGrid;
use App\Components\Job\IJobDataViewFactory;
use App\Components\Job\JobDataView;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
use App\Model\Entity\Role;
use App\Model\Facade\JobFacade;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;

class JobsPresenter extends BasePresenter
{
	// <editor-fold desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var IJobsGridFactory @inject */
	public $iJobsGridFactory;

	/** @var IJobDataViewFactory @inject */
	public $iJobDataViewFactory;
	
	/** @var JobFacade @inject */
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
			$message = $this->translator->translate('Finded company isn\'t exists.');
			$this->flashMessage($message, 'danger');
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
		$message = $this->translator->translate('Not implemented yet');
		$this->flashMessage($message, 'warning');
		$this->redirect('Dashboard:');
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('showAll')
	 */
	public function actionShowAll()
	{
		if(in_array(Role::COMPANY, $this->getUser()->getRoles())) {
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

	/** @return JobDataView */
	public function createComponentJobsDataView()
	{
		$control = $this->iJobDataViewFactory->create();
		return $control;
	}

	// </editor-fold>
}
