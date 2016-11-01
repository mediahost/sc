<?php

namespace App\AppModule\Presenters;

use App\Components\Job\BasicInfo;
use App\Components\Job\IBasicInfoFactory;
use App\Components\Job\ISkillsFactory;
use App\Components\Job\Skills;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
use App\Model\Facade\JobFacade;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;

class JobPresenter extends BasePresenter
{

	/** @var Job */
	private $job;

	// <editor-fold desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var JobFacade @inject */
	public $jobFacade;

	/** @var IBasicInfoFactory @inject */
	public $iJobBasicInfoFactory;

	/** @var ISkillsFactory @inject */
	public $iJobSkillsFactory;

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var EntityDao */
	private $jobRepository;

	/** @var EntityDao */
	private $companyRepository;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->jobRepository = $this->em->getRepository(Job::getClassName());
		$this->companyRepository = $this->em->getRepository(Company::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('view')
	 */
	public function actionView($id)
	{
		$job = $this->jobRepository->find($id);
		if ($job) {
			$this->template->job = $job;
		} else {
			$message = $this->translator->translate('Finded job isn\'t exists.');
			$this->flashMessage($message, 'danger');
			$this->redirect('Dashboard:');
		}
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('candidates')
	 */
	public function actionCandidates($id)
	{
		$job = $this->jobRepository->find($id);
		if ($job) {
			$this['candidatesList']->addFilterJob($job);
			$this->template->job = $job;
		} else {
			$message = $this->translator->translate('Finded job isn\'t exists.');
			$this->flashMessage($message, 'danger');
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
		$company = $this->companyRepository->find($companyId);
		if ($company) {
			$this->job = new Job();
			$this->job->company = $company;
			$this['jobInfoForm']->setJob($this->job);
			$this->setView('edit');
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
		$this->job = $this->jobRepository->find($id);
		if ($this->job) {
			$this['jobInfoForm']->setJob($this->job);
		} else {
			$message = $this->translator->translate('Finded job isn\'t exists.');
			$this->flashMessage($message, 'danger');
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
	 * @privilege('editSkills')
	 */
	public function actionEditSkills($id)
	{
		$this->job = $this->jobRepository->find($id);
		if ($this->job) {
			$this['jobSkillsForm']->setJob($this->job);
		} else {
			$message = $this->translator->translate('Finded job isn\'t exists.');
			$this->flashMessage($message, 'danger');
			$this->redirect('Dashboard:');
		}
		$this->template->job = $this->job;
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('delete')
	 */
	public function actionDelete($id)
	{
		$this->jobFacade->delete($id);
		$this->redirect('Jobs:showAll');
	}

	// </editor-fold>
	// <editor-fold desc="forms">

	/** @return BasicInfo */
	public function createComponentJobInfoForm()
	{
		$control = $this->iJobBasicInfoFactory->create();
		$control->onAfterSave = function ($job, $redirectToNext = FALSE) {
			$message = $this->translator->translate('Job \'%job%\' was successfully saved.', ['job' => (string)$job]);
			$this->flashMessage($message, 'success');
			if ($redirectToNext) {
				$this->redirect('editSkills', $job->id);
			} else {
				$this->redirect('edit', $job->id);
			}
		};
		return $control;
	}

	/** @return Skills */
	public function createComponentJobSkillsForm()
	{
		$control = $this->iJobSkillsFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSave = function (Job $job) {
			$message = $this->translator->translate('Job \'%job%\' was successfully saved.', ['job' => (string)$job]);
			$this->flashMessage($message, 'success');
			$this->redirect('editSkills', $job->id);
		};
		return $control;
	}

	// </editor-fold>
}
