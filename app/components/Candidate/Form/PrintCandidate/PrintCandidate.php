<?php

namespace App\Components\Candidate\Form;

use App\Components\BaseControl;
use App\Components\Candidate\ICandidateNotesFactory;
use App\Components\Job\IAcceptReasonFactory;
use App\Components\Job\ICustomStateFactory;
use App\Components\Job\IInviteByMailFactory;
use App\Components\Job\IMatchNotesFactory;
use App\Extensions\Candidates\CandidatesList;
use App\Helpers;
use App\Model\Entity\Candidate;
use App\Model\Entity\Job;
use App\Model\Entity\Match;
use App\Model\Entity\Note;
use App\Model\Entity\User as UserEntity;
use App\Model\Facade\CandidateFacade;
use App\Model\Facade\JobFacade;
use App\Model\Facade\UserFacade;
use Nette\Application\UI\Multiplier;
use Nette\Security\User;
use Nette\Utils\Random;

class PrintCandidate extends BaseControl
{

	const MAX_IT_CATEGORIES = 2;
	const PRIMARY_JOBS_COUNT = 3;

	/** @var array */
	public $onReload = [];

	/** @var User @inject */
	public $user;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var JobFacade @inject */
	public $jobFacade;

	/** @var CandidateFacade @inject */
	public $candidateFacade;

	/** @var IMatchNotesFactory @inject */
	public $iMatchNotesFactory;

	/** @var ICandidateNotesFactory @inject */
	public $iCandidateNotesFactory;

	/** @var IAcceptReasonFactory @inject */
	public $iAcceptReasonFactory;

	/** @var ICustomStateFactory @inject */
	public $iCustomStateFactory;

	/** @var IInviteByMailFactory @inject */
	public $iInviteByMailFactory;

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

	/** @var bool */
	private $showNotes = FALSE;

	// <editor-fold defaultstate="collapsed" desc="template">

	public function render()
	{
		$this->template->id = $this->candidate->id . '-' . Random::generate();
		$this->template->candidate = $this->candidate;
		$this->template->cv = $this->candidate->cv;
		$this->template->person = $this->candidate->person;
		$this->template->user = $this->candidate->person->user;
		$this->template->selectedJob = $this->selectedJob;
		if ($this->selectedJob) {
			$this->template->match = $this->candidate->findMatch($this->selectedJob);
		}
		$this->template->selectedManager = $this->selectedManager;
		$this->template->canShowAll = $this->canShowAll;
		$this->template->showJobList = !$this->showAsCompany;
		$this->template->showNotes = $this->showNotes;
		$this->template->primaryJobsCount = self::PRIMARY_JOBS_COUNT;

		$this->template->preferedJobCategories = $this->getPreferedJobCategories();
		$this->template->skills = $this->getItSkills();
		$this->template->matchStates = Match::getStates();

		$jobRepo = $this->em->getRepository(Job::getClassName());
		$this->template->jobs = $jobRepo->findBy([], [
			'id' => 'DESC',
		]);
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

	public function setJob(Job $job = NULL, $showNotes = FALSE)
	{
		$this->selectedJob = $job;
		$this->showNotes = $showNotes;
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
		if (!count($this->candidate->cv->skillKnows)) {
			return [];
		}

		$skills = $counts = [];
		$counts = [];
		foreach ($this->candidate->cv->skillKnows as $skillKnow) {
			$skills[$skillKnow->skill->category->name][] = [
				'name' => $skillKnow->skill->name,
				'level' => $skillKnow->level,
			];
			$counts[$skillKnow->skill->category->name] =
				array_key_exists($skillKnow->skill->category->name, $counts) ? $counts[$skillKnow->skill->category->name] + 1 : 1;
		}
		arsort($counts);
		$i = 0;
		foreach ($skills as $key => $skill) {
			if ($i >= self::MAX_IT_CATEGORIES) {
				unset($counts[$key]);
			} else {
				$counts[$key] = $skill;
			}
			$i++;
		}
		return $counts;
	}

	// </editor-fold>
	// <editor-fold desc="handlers">

	public function handleMatch($jobId)
	{
		if ($jobId && $this->user->isAllowed('match')) {
			$jobRepo = $this->em->getRepository(Job::getClassName());
			$job = $jobRepo->find($jobId);
			if ($job) {
				$match = $this->candidateFacade->matchApprove($this->candidate, $job);
				$message = 'Candidate was ' . ($match->candidateApprove ? 'approved' : 'invited');
				$this->presenter->flashMessage($this->translator->translate($message), 'success');
			}
		}
		$this->reload();
	}

	public function handleAccept($jobId, $value = TRUE)
	{
		if ($jobId && $this->user->isAllowed('match')) {
			$jobRepo = $this->em->getRepository(Job::getClassName());
			$job = $jobRepo->find($jobId);
			if ($job) {
				$match = $this->candidateFacade->accept($this->candidate, $job, $value);
				$message = 'Candidate was ' . ($match->accept ? 'accepted' : 'rejected');
				$this->presenter->flashMessage($this->translator->translate($message), 'success');
			}
		}
		$this->reload();
	}

	public function handleReject($jobId)
	{
		$this->handleAccept($jobId, FALSE);
	}

	public function handleChangeState($jobId, $state)
	{
		if ($jobId && $state && $this->user->isAllowed('match')) {
			$jobRepo = $this->em->getRepository(Job::getClassName());
			$job = $jobRepo->find($jobId);
			if ($job) {
				$matchRepo = $this->em->getRepository(Match::getClassName());
				$match = $this->candidate->findMatch($job);
				if ($match) {
					$match->state = $state;
					$matchRepo->save($match);
				}
				$message = 'Candidate state was changed';
				$this->presenter->flashMessage($this->translator->translate($message), 'success');
			}
		}
		$this->reload();
	}

	private function reload()
	{
		if ($this->presenter->isAjax()) {
			$this->em->refresh($this->candidate);
			$this->redrawControl();
			$parent = $this->getParent();
			if ($parent instanceof Multiplier) {
				$parent = $parent->getParent();
			}
			if ($parent instanceof CandidatesList) {
				$parent->reload();
			}
			$this->onReload($this);
		} else {
			$this->redirect('this');
		}
	}

	// </editor-fold>
	// <editor-fold desc="forms">

	public function createComponentCompanyNotes()
	{
		$control = $this->iMatchNotesFactory->create();
		$control->setType(Note::TYPE_COMPANY);
		$control->setMatch($this->candidate->findMatch($this->selectedJob));
		$control->onAfterSave[] = function (Match $match) {
			$this->reload();
		};
		return $control;
	}

	public function createComponentAdminNotes()
	{
		$control = $this->iCandidateNotesFactory->create();
		$control->setCandidate($this->candidate);
		$control->onAfterSave[] = function (Candidate $candidate) {
			$this->reload();
		};
		return $control;
	}

	public function createComponentAcceptReason()
	{
		$control = $this->iAcceptReasonFactory->create();
		$control->setAccept();
		$control->setMatch($this->candidate->findMatch($this->selectedJob));
		$control->onAfterSave[] = function (Match $match) {
			$this->handleAccept($match->job->id, TRUE);
		};
		return $control;
	}

	public function createComponentRejectReason()
	{
		$control = $this->iAcceptReasonFactory->create();
		$control->setAccept(FALSE);
		$control->setMatch($this->candidate->findMatch($this->selectedJob));
		$control->onAfterSave[] = function (Match $match) {
			$this->handleAccept($match->job->id, FALSE);
		};
		return $control;
	}

	public function createComponentCustomState()
	{
		$control = $this->iCustomStateFactory->create();
		$control->setMatch($this->candidate->findMatch($this->selectedJob));
		$control->setAjax(TRUE, FALSE);
		$control->onAfterSave[] = function (Match $match) {
			$message = 'Candidate state was changed';
			$this->presenter->flashMessage($this->translator->translate($message), 'success');
			$this->reload();
		};
		return $control;
	}

	public function createComponentInviteByMail()
	{
		$control = $this->iInviteByMailFactory->create();
		$control->setCandidate($this->candidate);
		$control->onAfterSave[] = function (Match $match) {
			$message = 'Candidate was ' . ($match->candidateApprove ? 'approved' : 'invited');
			$this->presenter->flashMessage($this->translator->translate($message), 'success');
			$this->redirect('this');
		};
		$control->onAfterFail[] = function () {
			$message = 'Missing dealer or job';
			$this->presenter->flashMessage($this->translator->translate($message), 'warning');
			$this->redirect('this');
		};
		return $control;
	}

	// </editor-fold>

}

interface IPrintCandidateFactory
{

	/** @return PrintCandidate */
	function create();
}
