<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Model\Entity\Job;

class JobList extends BaseControl
{

	public function render()
	{
		$jobRepo = $this->em->getRepository(Job::getClassName());
		$this->template->jobs = $jobRepo->findAll();
		parent::render();
	}
}

interface IJobListFactory
{
	/** @return JobList */
	public function create();
}