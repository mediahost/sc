<?php

namespace App\Components\Candidate;


class CandidatePreview extends \App\Components\BaseControl {
    
    /** @var \App\Model\Entity\Cv */
    private $cv;
    
    
    /**
     * Renders control
     */
    public function render() {
        $this->setTemplateFile('candidatePreview');
        $this->template->cv = $this->cv;
        parent::render();
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