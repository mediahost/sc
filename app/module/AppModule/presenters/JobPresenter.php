<?php

namespace App\AppModule\Presenters;

use App\Components\Job\BasicInfo;
use App\Components\Job\IBasicInfoFactory;
use App\Components\Job\ISkillsFactory;
use App\Components\Job\Skills;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
use App\Model\Entity\Role;
use App\Model\Facade\CandidateFacade;
use App\Model\Facade\JobFacade;
use App\Model\Repository\CompanyRepository;
use App\Model\Repository\JobRepository;
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

	/** @var CandidateFacade @inject */
	public $candidateFacade;

	/** @var IBasicInfoFactory @inject */
	public $iJobBasicInfoFactory;

	/** @var ISkillsFactory @inject */
	public $iJobSkillsFactory;

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var JobRepository */
	private $jobRepo;

	/** @var CompanyRepository */
	private $companyRepo;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->jobRepo = $this->em->getRepository(Job::getClassName());
		$this->companyRepo = $this->em->getRepository(Company::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('job')
	 * @privilege('view')
	 */
	public function actionView($id)
	{
		$this->job = $this->jobRepo->find($id);
		if (!$this->job || ($this->company && $this->job->company->id !== $this->company->id)) {
			$message = $this->translator->translate('Finded job isn\'t exists.');
			$this->flashMessage($message, 'danger');
			$this->redirect('Jobs:');
		}
	}

	public function renderView()
	{
		if ($this->job) {
			$this->template->job = $this->job;
			if ($this->user->isInRole(Role::CANDIDATE)) {
				$candidate = $this->user->getIdentity()->candidate;
				$this->template->isApplied = $this->candidateFacade->isApplied($candidate, $this->job);
				$this->template->isInvited = $this->candidateFacade->isApproved($candidate, $this->job);
				$this->template->isMatched = $this->candidateFacade->isMatched($candidate, $this->job);
			}
		}
	}

	/**
	 * @secured
	 * @resource('job')
	 * @privilege('candidates')
	 */
	public function actionCandidates($id)
	{
		$this->job = $this->jobRepo->find($id);
		if (!$this->job || ($this->company && $this->job->company->id !== $this->company->id)) {
			$message = $this->translator->translate('Finded job isn\'t exists.');
			$this->flashMessage($message, 'danger');
			$this->redirect('Jobs:');
		} else {
			$this['candidatesList']->addFilterJob($this->job, TRUE);
			$this->template->job = $this->job;
		}
	}

	/**
	 * @secured
	 * @resource('job')
	 * @privilege('add')
	 */
	public function actionAdd($companyId)
	{
		$company = $this->companyRepo->find($companyId);
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
	 * @resource('job')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$this->job = $this->jobRepo->find($id);
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
	 * @resource('job')
	 * @privilege('editSkills')
	 */
	public function actionEditSkills($id)
	{
		$this->job = $this->jobRepo->find($id);
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
	// <editor-fold desc="handlers">

	public function handleApply($jobId)
	{
		if ($this->user->isInRole(Role::CANDIDATE) && $jobId) {
			$job = $this->jobRepo->find($jobId);
			$identity = $this->user->getIdentity();
			if ($job && isset($identity->person->candidate)) {
				$this->candidateFacade->matchApply($identity->person->candidate, $job);
			}
		}
		$this->redrawControl('applyBox');
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
			$this->redirect('view', $job->id);
		};
		return $control;
	}

	// </editor-fold>
}
