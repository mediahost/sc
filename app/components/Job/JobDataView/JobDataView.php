<?php

namespace App\Components\Job;

/**
 * Description of JobDataView
 *
 */
class JobDataView extends \App\Components\BaseControl 
{
	/** @var type */
	private $jobs;
	
	/**
	 * @inheritdoc
	 */
	public function render() {
		$this->template->jobs = $this->jobs;
		parent::render();
	}
	
	/**
	 * 
	 * @param \Doctrine\Common\Collections\ArrayCollection $jobs
	 * @return \App\Components\Job\JobDataView
	 */
	public function setJobs(\Doctrine\Common\Collections\ArrayCollection $jobs) 
	{
		$this->jobs = $jobs;
		return $this;
	}
}


/**
 * Definition IJobDataViewFactory
 * 
 */
interface IJobDataViewFactory
{

	/** @return JobDataView */
	function create();
}
