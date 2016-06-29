<?php

namespace App\AppModule\Presenters;
use Kdyby\Doctrine\DBALException;
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
    
    /**
	 * @secured
	 * @resource('jobCategories')
	 * @privilege('edit')
	 */
    public function actionEdit($categoryId) {
        $this->jobCategory = $this->jobFacade->findJobCategory($categoryId);
		if ($this->jobCategory) {
			$this['jobCategoryForm']->setJobCategory($this->jobCategory);
            $this->template->jobCategory = $this->jobCategory;
		} else {
			$this->flashMessage('This category wasn\'t found.', 'error');
			$this->redirect('default');
		}
    }
    
    /**
	 * @secured
	 * @resource('jobCategories')
	 * @privilege('delete')
	 */
    public function actionDelete($categoryId) {
        $this->jobCategory = $this->jobFacade->findJobCategory($categoryId);
		if ($this->jobCategory) {
			try {
				$this->jobFacade->deleteJobCategory($this->jobCategory);
				$message = new \App\TaggedString('Category \'%s\' was deleted.', (string) $this->jobCategory);
				$this->flashMessage($message, 'success');
			} catch (DBALException $exc) {
				$message = new \App\TaggedString('\'%s\' has child category or job. You can\'t delete it.', (string) $this->jobCategory);
				$this->flashMessage($message, 'danger');
			}
		} else {
			$this->flashMessage('Category was not found.', 'danger');
		}
		$this->redirect('default');
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
