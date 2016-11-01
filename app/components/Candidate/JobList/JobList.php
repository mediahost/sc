<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Model\Entity\Job;

class JobList extends BaseControl
{

	private $limit;

	public function render()
	{
		$jobRepo = $this->em->getRepository(Job::getClassName());
		$this->template->jobs = $jobRepo->findBy([], [], $this->limit);
		parent::render();
	}

	public function setLimit($value)
	{
		$this->limit = $value;
		return $this;
	}
}

interface IJobListFactory
{
	/** @return JobList */
	public function create();
}