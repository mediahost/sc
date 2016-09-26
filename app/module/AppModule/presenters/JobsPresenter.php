<?php

namespace App\AppModule\Presenters;

use App\Components\Grids\Job\IJobsGridFactory;
use App\Components\Grids\Job\JobsGrid;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
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
	
	/** @var JobFacade @inject */
	public $jobFacade;
	
	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var EntityDao */
	private $companyRepo;

	/** @var EntityDao */
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
	public function actionDefault($id)
	{
		$company = $this->companyRepo->find($id);
		if ($company) {
			$this['jobsGrid']->setCompany($company);
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
	 * @privilege('showAll')
	 */
	public function actionShowAll()
	{
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
	// <editor-fold desc="grids">

	/** @return JobsGrid */
	public function createComponentJobsGrid()
	{
		$control = $this->iJobsGridFactory->create();
		return $control;
	}
	// </editor-fold>
}
