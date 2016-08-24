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
            $category = reset($categories);
            $this->openJob = $category->id;
        }
        $this->setTemplateFile('MatchingControl');
        $this->template->categories = $categories;
        $this->template->openJob = $this->openJob;
        parent::render();
    }
    
    public function handleInvite($jobId, $cvId, $matched) {
        $job = $this->jobFacade->find($jobId);
        if (!$job) {
	        $message = $this->translator->translate('Job was not found');
            $this->presenter->flashMessage($message);
            $this->presenter->sendPayload();
        }
        $cv = $this->cvFacade->find($cvId);
        if (!$cv) {
	        $message = $this->translator->translate('Cv was not found');
            $this->presenter->flashMessage($message);
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
	            $message = $this->translator->translate('Cv was invited to job');
                $this->presenter->flashMessage($message);
            }
        }
        foreach ($job->getCvsState() as $cvId => $state) {
            if (!in_array($cvId, $cvs)) {
                $this->jobFacade->detach($job, $cvId);
	            $message = $this->translator->translate('Cv was detached from job');
                $this->presenter->flashMessage($message);
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