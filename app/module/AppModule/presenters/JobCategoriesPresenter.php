<?php

namespace App\AppModule\Presenters;
use App\Components\Job\IJobCategoryDataViewFactory;


class JobCategoriesPresenter extends BasePresenter {
    
    /** @var IJobCategoryDataViewFactory    @inject */
	public $jobCategoryDataViewFactory;
    
    
    /**
	 * @secured
	 * @resource('jobCategories')
	 * @privilege('default')
	 */
	public function renderDefault()
	{

	}
    
    public function createComponentJobCategoryDataView() {
        $control = $this->jobCategoryDataViewFactory->create();
        return $control;
    }
}
