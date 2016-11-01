<?php

namespace App\Components\Candidate\Form;

use App\Components\BaseControl;
use App\Helpers;
use App\Model\Entity\Candidate;
use App\Model\Entity\Job;
use App\Model\Entity\Sign;
use App\Model\Entity\Stock;
use App\Model\Facade\BasketFacade;
use App\Model\Facade\CandidateFacade;
use App\Model\Facade\Exception\InsufficientQuantityException;
use App\Model\Facade\JobFacade;
use App\Model\Facade\UserFacade;
use Nette\Security\User;
use Nette\Utils\Random;

class PrintCandidate extends BaseControl
{

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

	/** @var bool */
	private $canShowAll;

	// <editor-fold defaultstate="collapsed" desc="template">

	public function render()
	{
		$this->template->id = $this->candidate->id . '-' . Random::generate();
		$this->template->candidate = $this->candidate;
		$this->template->cv = $this->candidate->cv;
		$this->template->person = $this->candidate->person;
		$this->template->user = $this->candidate->person->user;
		$this->template->selectedJob = $this->selectedJob;
		$this->template->canShowAll = $this->canShowAll;

		$this->template->preferedJobCategories = $this->getPreferedJobCategories();
		$this->template->skills = $this->getItSkills();

		$jobRepo = $this->em->getRepository(Job::getClassName());
		$this->template->jobs = $jobRepo->findAll();

		$this->template->identity = $this->user;
		$this->template->addFilter('canAccess', $this->userFacade->canAccess);
		parent::render();
	}

	// </editor-fold>
	// <editor-fold desc="setters & getters">

	public function setCandidateById($candidateId, $canShowAll = FALSE, Job $job = NULL)
	{
		$candidateRepo = $this->em->getRepository(Candidate::getClassName());
		$candidate = $candidateRepo->find($candidateId);
		if ($candidate) {
			$this->setCandidate($candidate, $canShowAll, $job);
		}
		return $this;
	}

	public function setCandidate(Candidate $candidate, $canShowAll = FALSE, Job $job = NULL)
	{
		$this->candidate = $candidate;
		$this->canShowAll = $canShowAll;
		$this->selectedJob = $job;
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
				$this->candidateFacade->matchIntern($this->candidate, $job);
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
