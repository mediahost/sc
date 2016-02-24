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
	
	/** @var \App\Model\Entity\Company */
	private $company;
	
	/**
	 * @inheritdoc
	 */
	public function render() {
		$this->template->jobs = $this->jobs;
		$this->template->company = $this->company;
		parent::render();
	}
	
	/**
	 * @param \Doctrine\Common\Collections\ArrayCollection $jobs
	 * @return \App\Components\Job\JobDataView
	 */
	public function setJobs(\Doctrine\Common\Collections\ArrayCollection $jobs) 
	{
		$this->jobs = $jobs;
		return $this;
	}
	
	/**
	 * @param \App\Model\Entity\Company $company
	 * @return \App\Components\Job\JobDataView
	 */
	public function setCompany(\App\Model\Entity\Company $company) {
		$this->company = $company;
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
