<?php

namespace App\AppModule\Presenters;

use App\Components\Job\IJobControlFactory;
use App\Components\Job\JobControl;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
use App\TaggedString;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;

/**
 * Job presenter.
 */
class JobPresenter extends BasePresenter
{

	/** @persistent int */
	public $companyId;

	/** @var Job */
	private $job;

	// <editor-fold defaultstate="expanded" desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var IJobControlFactory @inject */
	public $iJobControlFactory;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var EntityDao */
	private $jobDao;

	/** @var EntityDao */
	private $companyDao;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->jobDao = $this->em->getDao(Job::getClassName());
		$this->companyDao = $this->em->getDao(Company::getClassName());
	}

	// <editor-fold defaultstate="expanded" desc="actions & renderers">

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('view')
	 */
	public function actionView($id)
	{
		$this->flashMessage('Not implemented yet.', 'warning');
		$this->redirect('default');
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('add')
	 */
	public function actionAdd($companyId)
	{
		$company = $this->companyDao->find($companyId);
		if ($company) {
			$this->job = new Job;
			$this->job->company = $company;
			$this['jobForm']->setJob($this->job);
		} else {
			$this->flashMessage('Finded company isn\'t exists.', 'warning');
			$this->redirect('Dashboard:');
		}
		$this->setView('edit');
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$this->job = $this->jobDao->find($id);
		if ($this->job) {
			$this['jobForm']->setJob($this->job);
		} else {
			$this->flashMessage('Finded job isn\'t exists.', 'warning');
			$this->redirect('Dashboard:');
		}
	}

	public function renderEdit()
	{
		$this->template->job = $this->job;
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('delete')
	 */
	public function actionDelete($id)
	{
		$this->flashMessage('Not implemented yet.', 'warning');
		$this->redirect('default');
	}

	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="edit/delete priviledges">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="forms">

	/** @return JobControl */
	public function createComponentJobForm()
	{
		$control = $this->iJobControlFactory->create();
		$control->onAfterSave = function (Job $job) {
			$message = new TaggedString('Job \'%s\' was successfully saved.', (string) $job);
			$this->flashMessage($message, 'success');
			$this->redirect('Jobs:', $job->company->id);
		};
		return $control;
	}

	// </editor-fold>
}
