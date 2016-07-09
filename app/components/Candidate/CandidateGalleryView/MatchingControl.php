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
            if(!$job->hasMatchedCv($cvId)) {
                $cv = $this->cvFacade->find($cvId);
                $this->jobFacade->invite($job, $cv);
                $this->presenter->flashMessage('Cv was invited to job');
            }
        }
    }
}

interface IMatchingControlFactory
{

	/** @return MatchingControl */
	function create();
}