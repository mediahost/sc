<?php

namespace App\Components\Candidate;

use App\Model\Facade\JobFacade;
use App\Model\Facade\CvFacade;
use App\Model\Entity\JobCategory;


class MatchingControl extends \App\Components\BaseControl {
    
    /** @var JobFacade @inject */
	public $jobFacade;
    
    /** @var CvFacade @inject */
	public $cvFacade;
    
    /** @var JobCategory[] */
    private $categories;
    
    
    /**
     * Renders control
     */
    public function render() {
        $this->setTemplateFile('MatchingControl');
        $this->template->categories = $this->jobFacade->findCategories();
        parent::render();
    }
    
    public function handleInvite($jobId, $cvs) {
        $job = $this->jobFacade->find($jobId);
        $cvs = explode(',', $cvs);
        foreach ($cvs as $cvId) {
            $cv = $this->cvFacade->find($cvId);
            if ($cv) {
                $this->jobFacade->invite($job, $cv);
                $this->presenter->flashMessage('Cv was invited to job');
            }
        }
        foreach ($job->getCvsState() as $cvId => $state) {
            if (!in_array($cvId, $cvs)) {
                $this->jobFacade->detach($job, $cvId);
                $this->presenter->flashMessage('Cv was detached from job');
            }
        }
        $this->redrawControl('matchingControl');
    }
    
    public function getMatchedCvs($job) {
        $result = [];
        foreach ($job->getCvsState() as $cvId => $cvState) {
            $result[] = sprintf("%d|%d", $cvId, $cvState);
        }
        return implode(',', $result);
    }
}

interface IMatchingControlFactory
{

	/** @return MatchingControl */
	function create();
}