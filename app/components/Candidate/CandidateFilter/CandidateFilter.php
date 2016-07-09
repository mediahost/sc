<?php

namespace App\Components\Candidate;


class CandidateFilter extends \App\Components\BaseControl {
    
    
    
}

interface ICandidateFilterFactory
{

	/** @return CandidateFilter */
	function create();
}