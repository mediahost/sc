<?php

namespace App\Components\Candidate\Form;

use App\Components\BaseControl;
use App\Helpers;
use App\Model\Entity\Candidate;
use App\Model\Entity\Job;
use App\Model\Entity\User as UserEntity;
use App\Model\Facade\CandidateFacade;
use App\Model\Facade\JobFacade;
use App\Model\Facade\UserFacade;
use Nette\Security\User;
use Nette\Utils\Random;

class PrintCandidate extends BaseControl
{

	const PRIMARY_JOBS_COUNT = 3;

	/** @var User @inject */
	public $user;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var JobFacade @inject */
	public $jobFacade;

	/** @var CandidateFacade @inject */
	public $candidateFacade;

	/** @var Candidate */
	private $candidate;

	/** @var Job */
	private $selectedJob;

	/** @var UserEntity */
	private $selectedManager;

	/** @var bool */
	private $canShowAll;

	/** @var bool */
	private $showAsCompany;

	// <editor-fold defaultstate="collapsed" desc="template">

	public function render()
	{
		$this->template->id = $this->candidate->id . '-' . Random::generate();
		$this->template->candidate = $this->candidate;
		$this->template->cv = $this->candidate->cv;
		$this->template->person = $this->candidate->person;
		$this->template->user = $this->candidate->person->user;
		$this->template->selectedJob = $this->selectedJob;
		$this->template->selectedManager = $this->selectedManager;
		$this->template->canShowAll = $this->canShowAll;
		$this->template->showJobList = !$this->showAsCompany;
		$this->template->primaryJobsCount = self::PRIMARY_JOBS_COUNT;

		$this->template->preferedJobCategories = $this->getPreferedJobCategories();
		$this->template->skills = $this->getItSkills();

		$jobRepo = $this->em->getRepository(Job::getClassName());
		$this->template->jobs = $jobRepo->findAll();
		$this->template->primaryJobs = $this->template->jobs;
		if ($this->selectedManager) {
			$this->template->primaryJobs = $jobRepo->findBy([
				'accountManager' => $this->selectedManager,
			], [], self::PRIMARY_JOBS_COUNT);
		}

		$this->template->identity = $this->user;
		$this->template->addFilter('canAccess', $this->userFacade->canAccess);
		parent::render();
	}

	// </editor-fold>
	// <editor-fold desc="setters & getters">

	public function setCandidateById($candidateId, Job $job = NULL, UserEntity $manager = NULL)
	{
		$candidateRepo = $this->em->getRepository(Candidate::getClassName());
		$candidate = $candidateRepo->find($candidateId);
		if ($candidate) {
			$this->setCandidate($candidate, $job, $manager);
		}
		return $this;
	}

	public function setCandidate(Candidate $candidate)
	{
		$this->candidate = $candidate;
		return $this;
	}

	public function setJob(Job $job = NULL)
	{
		$this->selectedJob = $job;
		return $this;
	}

	public function setAccountManager(UserEntity $manager = NULL)
	{
		$this->selectedManager = $manager;
		return $this;
	}

	public function setShow($canShowAll = FALSE, $showAsCompany = FALSE)
	{
		$this->canShowAll = $canShowAll;
		$this->showAsCompany = $showAsCompany;
		return $this;
	}

	public function findMatch(Job $job)
	{
		return $this->candidateFacade->findMatch($this->candidate, $job);
	}

	private function getPreferedJobCategories()
	{
		$prefered = NULL;
		foreach ($this->candidate->jobCategories as $category) {
			$prefered = Helpers::concatStrings(', ', $prefered, (string)$category);
		}
		return $prefered;
	}

	private function getItSkills()
	{
		$skills = [];
		foreach ($this->candidate->cv->skillKnows as $skillKnow) {
			$skills[] = $skillKnow->skill->name;
		}
		$result = implode(', ', $skills);
		return $result;
	}

	// </editor-fold>

	public function handleMatch($jobId)
	{
		if ($jobId) {
			$jobRepo = $this->em->getRepository(Job::getClassName());
			$job = $jobRepo->find($jobId);
			if ($job) {
				$match = $this->candidateFacade->matchApprove($this->candidate, $job);
				if (!$match->candidateApprove) {
					$message = 'Candidate was invited';
				} else {
					$message = 'Candidate was approved';
				}
				$this->flashMessage($this->translator->translate($message), 'success');
			}
		}

		if ($this->presenter->isAjax()) {
			$this->redrawControl();
			$this->presenter->redrawControl();
			if (isset($this->presenter['candidatesList'])) {
				$this->presenter['candidatesList']->redrawControl();
			}
		} else {
			$this->redirect('this');
		}
	}
}

interface IPrintCandidateFactory
{

	/** @return PrintCandidate */
	function create();
}
