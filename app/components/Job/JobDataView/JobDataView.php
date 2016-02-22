<?php

namespace App\Components\Job;

/**
 * Description of JobDataView
 *
 */
class JobDataView extends \App\Components\BaseControl {
	
	/** @var \App\Model\Facade\JobFacade */
	private $jobFacade;
	
	
	/**
	 * @param \App\Model\Facade\JobFacade $jobFacade
	 */
	public function __construct(\App\Model\Facade\JobFacade $jobFacade) {
		parent::__construct();
		$this->jobFacade = $jobFacade;
	}
	
	/**
	 * @inheritdoc
	 */
	public function render() {
		$this->template->jobs = $this->jobFacade->findAll();
		parent::render();
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
