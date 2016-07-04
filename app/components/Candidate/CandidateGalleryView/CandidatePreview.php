<?php

namespace App\Components\Candidate;
use App\Model\Facade\JobFacade;
use App\Model\Facade\SkillFacade;


class CandidatePreview extends \App\Components\BaseControl {
    
    /** @var JobFacade @inject */
    public $jobFacade;
    
    /** @var SkillFacade @inject */
    public $skillFacade;
    
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
        parent::render();
    }
    
    private function getPreferedJobCategories() {
        $result = [];
        $categories = $this->jobFacade->findCandidatePreferedCategories($this->cv->candidate);
        if (count($categories) > 3) {
            $result['short'] = implode(', ', array_slice($categories, 0, 2));
            $result['full'] = implode(',', $categories);
        } else {
            $result['short'] = implode(', ', $categories);
        }
        return $result;
    }
    
    private function getItSkills() {
        $skills = [];
        foreach ($this->cv->skillKnows as $skillKnow) {
            $skills[] = $skillKnow->skill->name;
        }
        if (count($skills) > 3) {
            $result['short'] = implode(', ', array_slice($skills, 0, 2));
            $result['full'] = implode(',', $skills);
        } else {
            $result['short'] = implode(', ', $skills);
        }
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