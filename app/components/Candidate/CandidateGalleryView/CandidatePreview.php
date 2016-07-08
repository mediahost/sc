<?php

namespace App\Components\Candidate;
use App\Model\Facade\JobFacade;
use App\Model\Facade\SkillFacade;
use App\Model\Facade\UserFacade;


class CandidatePreview extends \App\Components\BaseControl {
    
    /** @var JobFacade @inject */
    public $jobFacade;
    
    /** @var SkillFacade @inject */
    public $skillFacade;
    
    /** @var UserFacade @inject */
    public $userFacade;
    
    /** @var \App\Model\Entity\Cv */
    private $cv;
    
    
    /**
     * Renders control
     */
    public function render() {
        $this->setTemplateFile('CandidatePreview');
        $this->template->cv = $this->cv;
        $this->template->preferedJobCategories = $this->getPreferedJobCategories();
        $this->template->skills = $this->getItSkills();
        $this->template->addFilter('canAccess', $this->userFacade->canAccess);
        parent::render();
    }
    
    private function getPreferedJobCategories() {
        $categories = $this->jobFacade->findCandidatePreferedCategories($this->cv->candidate);
        $result = implode(', ', $categories);
        return $result;
    }
    
    private function getItSkills() {
        $skills = [];
        foreach ($this->cv->skillKnows as $skillKnow) {
            $skills[] = $skillKnow->skill->name;
        }
        $result = implode(', ', $skills);
        return $result;
    }

    /**
     * Setter for $cv
     * @param \App\Model\Entity\Cv $cv
     * @return \App\Components\Candidate\CandidatePreview
     */
    public function setCv(\App\Model\Entity\Cv $cv) {
        $this->cv = $cv;
        return $this;
    }
}

interface ICandidatePreviewFactory
{

	/** @return CandidatePreview */
	function create();
}