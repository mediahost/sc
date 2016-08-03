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
    
    /** @var int */
    private $openJob;
    
    
    /**
     * Renders control
     */
    public function render() {
        $categories = $this->jobFacade->findCategories();
        if (!$this->openJob) {
            $this->openJob = $categories[0]->id;
        }
        $this->setTemplateFile('MatchingControl');
        $this->template->categories = $categories;
        $this->template->openJob = $this->openJob;
        parent::render();
    }
    
    public function handleInvite($jobId, $cvId, $matched) {
        $job = $this->jobFacade->find($jobId);
        if (!$job) {
            $this->presenter->flashMessage('Job was not found');
            $this->presenter->sendPayload();
        }
        $cv = $this->cvFacade->find($cvId);
        if (!$cv) {
            $this->presenter->flashMessage('Cv was not found');
            $this->presenter->sendPayload();
        }
        if ($matched) {
            $this->jobFacade->invite($job, $cv);
        } else {
            $this->jobFacade->detach($job, $cvId);
        }
        $this->openJob = $jobId;
        $this->redrawControl('matchingControl');
    }
    
    public function handleInvite1($jobId, $cvs) {
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
        $this->presenter->payload->cvs = $this->getMatchedCvs($job);
        $this->presenter->sendPayload();
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