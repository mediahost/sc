<?php

namespace App\AppModule\Presenters;

use App\Components\AfterRegistration\CompleteCv;
use App\Components\AfterRegistration\ICompleteCvFactory;
use App\Components\Job\BasicInfo;
use App\Components\Job\IBasicInfoFactory;
use App\Components\Job\ISkillsFactory;
use App\Components\Job\Skills;
use App\Extensions\Candidates\CandidatesList;
use App\Model\Entity\Candidate;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
use App\Model\Entity\Match;
use App\Model\Entity\Role;
use App\Model\Facade\ActionFacade;
use App\Model\Facade\CandidateFacade;
use App\Model\Facade\JobFacade;
use App\Model\Repository\CompanyRepository;
use App\Model\Repository\JobRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Multiplier;

class JobPresenter extends BasePresenter
{

	/** @var Job */
	private $job;

	// <editor-fold desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var JobFacade @inject */
	public $jobFacade;

	/** @var ActionFacade @inject */
	public $actionFacade;

	/** @var CandidateFacade @inject */
	public $candidateFacade;

	/** @var IBasicInfoFactory @inject */
	public $iJobBasicInfoFactory;

	/** @var ISkillsFactory @inject */
	public $iJobSkillsFactory;

	/** @var ICompleteCvFactory @inject */
	public $iCompleteCvFactory;

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
		} else if ($this->user->isAllowed('match')) {
			$allowedStates = [
				Match::STATE_MATCHED_ONLY,
				Match::STATE_REJECTED,
				Match::STATE_ACCEPTED_ONLY,
				Match::STATE_INVITED_FOR_IV,
				Match::STATE_COMPLETE_IV,
				Match::STATE_OFFERED,
			];
			if ($this->user->isAllowed('job', 'showNotMatched')) {
				$allowedStates = array_merge([
					Match::STATE_APPLIED_ONLY,
					Match::STATE_INVITED_ONLY,
				], $allowedStates);
			}
			$this->template->allowedStates = Match::getStateName($allowedStates);
			foreach ($allowedStates as $stateKey) {
				$this['jobCandidates-' . $stateKey]->setCandidateOnReload(function () use ($stateKey, $allowedStates) {
					foreach ($allowedStates as $key) {
						if ($key != $stateKey) {
							$this['jobCandidates-' . $key]->reload();
						}
					}
				});
			}
		}

		$this->actionFacade->addJobView($this->user->identity, $this->job);

		if ($this->user->isInRole(Role::CANDIDATE)) {
			$candidate = $this->user->getIdentity()->person->candidate;
			$this['uploadCv']->setCandidate($candidate);
		}
	}

	public function renderView()
	{
		if ($this->job) {
			$this->template->job = $this->job;
			if ($this->user->isInRole(Role::CANDIDATE)) {
				$candidate = $this->user->getIdentity()->person->candidate;
				$this->template->candidate = $candidate;
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
	public function actionCandidates($id, $state = NULL)
	{
		$this->job = $this->jobRepo->find($id);
		if (!$this->job || ($this->company && $this->job->company->id !== $this->company->id)) {
			$message = $this->translator->translate('Finded job isn\'t exists.');
			$this->flashMessage($message, 'danger');
			$this->redirect('Jobs:');
		} else {
			if (!Match::isAcceptedState($state)) {
				$state = Match::STATE_APPROVED;
			}
			$this['candidatesList']->addFilterJob($this->job, TRUE, $state);
			$this->template->job = $this->job;
			if ($state) {
				$this->template->stateName = Match::getStateName($state);
			}
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

	public function handleApply($jobId, $redirectUrl = NULL)
	{
		if ($this->user->isInRole(Role::CANDIDATE) && $jobId) {
			$job = $this->jobRepo->find($jobId);
			$identity = $this->user->getIdentity();
			if ($job && isset($identity->person->candidate)) {
				/** @var Candidate $candidate */
				$candidate = $identity->person->candidate;

				if ($candidate->isApplyable()) {
					$this->candidateFacade->matchApply($candidate, $job);
					$message = $this->translator->translate('Thank you for applying for this job, someone will be in touch with you soon');
					$this->flashMessage($message, 'info');
					$this->em->refresh($candidate);

					$this->actionFacade->addJobApply($this->user->identity, $job);
				} else {
					$message = $this->translator->translate('You cannot apply for this job. You must upload your CV file at first.');
					$this->flashMessage($message, 'warning');
					$redirectUrl = NULL;
				}
			}
		}
		if ($this->isAjax()) {
			$this->redrawControl();
		} else if ($redirectUrl) {
			$this->redirectUrl($redirectUrl);
		} else {
			$this->redirect('this');
		}
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

	/** @return CandidatesList */
	public function createComponentJobCandidates()
	{
		return new Multiplier(function ($state) {
			$control = $this->iCandidatesListFactory->create();
			$control->setTranslator($this->translator)
				->setItemsPerPage(4, 15)
				->setAjax()
				->addFilterJob($this->job, TRUE, $state);
			return $control;
		});
	}

	/** @return CompleteCv */
	public function createComponentUploadCv()
	{
		$control = $this->iCompleteCvFactory->create();
		$control->setModal();
		$control->onAfterSave[] = function (CompleteCv $control, Candidate $candidate, $jobApplyId, $redirectUrl) {
			$message = $this->translator->translate('File was successfully uploaded.');
			$this->flashMessage($message, 'success');
			if ($jobApplyId) {
				$this->handleApply($jobApplyId, $redirectUrl);
			} else if($this->job) {
				$this->handleApply($this->job->id, $redirectUrl);
			}
			if ($redirectUrl) {
				$this->redirectUrl($redirectUrl);
			}
			$this->redirect('this');
		};
		return $control;
	}

	// </editor-fold>

}
