<?php

namespace App\Components\Candidate;


class CandidateFilter extends \App\Components\BaseControl {
    
    public function render() {
        $this->template->states = \App\Model\Entity\JobCv::getStates();
        parent::render();
    }
    
}

interface ICandidateFilterFactory
{

	/** @return CandidateFilter */
	function create();
}