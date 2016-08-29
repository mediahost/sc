<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Model\Entity\Cv;
use App\Model\Entity\Job;
use App\Model\Facade\JobFacade;
use App\Model\Facade\SkillFacade;
use App\Model\Facade\UserFacade;

class CandidatePreview extends BaseControl
{

	/** @var JobFacade @inject */
	public $jobFacade;

	/** @var SkillFacade @inject */
	public $skillFacade;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var Cv */
	private $cv;

	public function render()
	{
		$this->setTemplateFile('CandidatePreview');
		$this->template->cv = $this->cv;
		$this->template->preferedJobCategories = $this->getPreferedJobCategories();
		$this->template->skills = $this->getItSkills();
		$this->template->addFilter('canAccess', $this->userFacade->canAccess);
		parent::render();
	}

	public function renderJobView(Job $job)
	{
		$this->setTemplateFile('CandidateJobView');
		$this->template->cv = $this->cv;
		$this->template->job = $job;
		$this->template->preferedJobCategories = $this->getPreferedJobCategories();
		$this->template->skills = $this->getItSkills();
		$this->template->addFilter('canAccess', $this->userFacade->canAccess);
		parent::render();
	}

	public function getCvState(Cv $cv, Job $job)
	{
		return $job->getStateEntity($cv->id)->state;
	}

	private function getPreferedJobCategories()
	{
		$categories = $this->jobFacade->findCandidatePreferedCategories($this->cv->candidate);
		$result = implode(', ', $categories);
		return $result;
	}

	private function getItSkills()
	{
		$skills = [];
		foreach ($this->cv->skillKnows as $skillKnow) {
			$skills[] = $skillKnow->skill->name;
		}
		$result = implode(', ', $skills);
		return $result;
	}

	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}
}

interface ICandidatePreviewFactory
{

	/** @return CandidatePreview */
	function create();
}