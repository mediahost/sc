<?php

namespace App\Components\Candidate;

use App\Model\Facade\JobFacade;
use App\Model\Entity\JobCategory;


class MatchingControl extends \App\Components\BaseControl {
    
    /** @var JobFacade @inject */
	public $jobFacade;
    
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
}

interface IMatchingControlFactory
{

	/** @return MatchingControl */
	function create();
}