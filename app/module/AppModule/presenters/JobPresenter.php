<?php

namespace App\AppModule\Presenters;

use App\Components\Job\BasicInfoControl;
use App\Components\Job\IBasicInfoControlFactory;
use App\Components\Job\ISkillsControlFactory;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
use App\Model\Facade\JobFacade;
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

	/** @var JobFacade @inject */
	public $jobFacade;

	/** @var IBasicInfoControlFactory @inject */
	public $iJobBasicInfoControlFactory;

	/** @var ISkillsControlFactory @inject */
	public $iJobSkillsControlFactory;

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
		$job = $this->jobDao->find($id);
		if ($job) {
			$this->template->job = $job;
			$this->template->matchedCvs = $this->jobFacade->findCvs($job);
		} else {
			$this->flashMessage('Finded job isn\'t exists.', 'danger');
			$this->redirect('Dashboard:');
		}
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
			$this['jobInfoForm']->setJob($this->job);
			$this['jobSkillsForm']->setJob($this->job);
		} else {
			$this->flashMessage('Finded company isn\'t exists.', 'danger');
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
			$this['jobInfoForm']->setJob($this->job);
			$this['jobSkillsForm']->setJob($this->job);
		} else {
			$this->flashMessage('Finded job isn\'t exists.', 'danger');
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

	/** @return BasicInfoControl */
	public function createComponentJobInfoForm()
	{
		$control = $this->iJobBasicInfoControlFactory->create();
		$control->onAfterSave = function (Job $job) {
			$message = new TaggedString('Job \'%s\' was successfully saved.', (string) $job);
			$this->flashMessage($message, 'success');
			$this->redirect('Jobs:', $job->company->id);
		};
		return $control;
	}

	/** @return BasicInfoControl */
	public function createComponentJobSkillsForm()
	{
		$control = $this->iJobSkillsControlFactory->create();
		$control->onAfterSave = function (Job $job) {
			$message = new TaggedString('Job \'%s\' was successfully saved.', (string) $job);
			$this->flashMessage($message, 'success');
			$this->redirect('Jobs:', $job->company->id);
		};
		return $control;
	}

	// </editor-fold>
}
