<?php

namespace App\Components\Candidate;

class SkillFilter extends \App\Components\BaseControl {
    
    /**
     * Renders control
     */
    public function render() {
        $this->setTemplateFile('SkillFilter');
        parent::render();
    }
    
    /**
     * Renders control preview
     */
    public function renderPreview() {
        $this->setTemplateFile('SkillFilterPreview');
        parent::render();
    }
}

interface ISkillFilterFactory
{

	/** @return SkillFilter */
	function create();
}