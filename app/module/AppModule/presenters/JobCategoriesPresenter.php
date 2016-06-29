<?php

namespace App\AppModule\Presenters;
use App\Components\Job\IJobCategoryDataViewFactory;
use App\Components\Job\IJobCategoryControlFactory;
use App\Model\Facade\JobFacade;


class JobCategoriesPresenter extends BasePresenter {
    
    /** @var IJobCategoryDataViewFactory    @inject */
	public $jobCategoryDataViewFactory;
    
    /** @var IJobCategoryControlFactory    @inject */
	public $jobCategoryControlFactory;
    
    /** @var JobFacade    @inject */
	public $jobFacade;
    
    /** @var \App\Model\Entity\JobCategory */
    private $jobCategory;




    /**
	 * @secured
	 * @resource('jobCategories')
	 * @privilege('default')
	 */
	public function renderDefault()
	{
        
    }
    
    /**
	 * @secured
	 * @resource('jobCategories')
	 * @privilege('add')
	 */
	public function actionAdd()
	{
        $this->jobCategory = new \App\Model\Entity\JobCategory();
		$this['jobCategoryForm']->setJobCategory($this->jobCategory);
        $this->template->jobCategory = $this->jobCategory;
		$this->setView('edit');
	}
    
    public function actionEdit($categoryId) {
        
    }
    
    public function actionDelete($categoryId) {
        
    }
    
    public function createComponentJobCategoryForm() {
        $control = $this->jobCategoryControlFactory->create();
        $control->onAfterSave = function (\App\Model\Entity\JobCategory $saved) {
			$message = new \App\TaggedString('\'%s\' was successfully saved.', (string) $saved);
			$this->flashMessage($message, 'success');
			$this->redirect('default');
		};
        return $control;
    }
    
    public function createComponentJobCategoryDataView() {
        $control = $this->jobCategoryDataViewFactory->create();
        $control->setJobCategories($this->jobFacade->findCategories());
        return $control;
    }
}
