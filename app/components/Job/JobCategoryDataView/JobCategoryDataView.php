<?php

namespace App\Components\Job;
use App\Components\BaseControl;


class JobCategoryDataView extends BaseControl {
    
    /** @var array */
    private $jobCategories;
    
    /**
     * @inheritdoc
     */
    public function render()
    {
        $this->template->jobCategories = $this->jobCategories;
        parent::render();
    }
    
    /**
     * Setter form $jobCategories
     * @param $jobCategories
     * @return $this
     */
    public function setJobCategories($jobCategories)
    {
        $this->jobCategories = $jobCategories;
        return $this;
    }
}


/**
 * Interface IJobCategoryDataViewFactory
 * @package App\Components\Jobs
 */
Interface IJobCategoryDataViewFactory
{
    /** @return JobCategoryDataView */
    public function create();
}