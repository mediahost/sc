<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Model\Entity\JobCv;

class CandidateFilter extends BaseControl
{

	public function render()
	{
		$this->template->states = JobCv::getStates();
		parent::render();
	}

}

interface ICandidateFilterFactory
{

	/** @return CandidateFilter */
	function create();
}