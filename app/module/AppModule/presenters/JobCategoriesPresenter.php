<?php

namespace App\AppModule\Presenters;

use App\Components\Job\IJobCategoryFactory;
use App\Components\Job\IJobCategoryDataViewFactory;
use App\Model\Entity\JobCategory;
use App\Model\Facade\JobFacade;
use Kdyby\Doctrine\DBALException;

class JobCategoriesPresenter extends BasePresenter
{

	/** @var IJobCategoryDataViewFactory @inject */
	public $jobCategoryDataViewFactory;

	/** @var IJobCategoryFactory @inject */
	public $jobCategoryFactory;

	/** @var JobFacade @inject */
	public $jobFacade;

	/** @var JobCategory */
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
		$this->jobCategory = new JobCategory();
		$this['jobCategoryForm']->setJobCategory($this->jobCategory);
		$this->template->jobCategory = $this->jobCategory;
		$this->setView('edit');
	}

	/**
	 * @secured
	 * @resource('jobCategories')
	 * @privilege('edit')
	 */
	public function actionEdit($categoryId)
	{
		$this->jobCategory = $this->jobFacade->findJobCategory($categoryId);
		if ($this->jobCategory) {
			$this['jobCategoryForm']->setJobCategory($this->jobCategory);
			$this->template->jobCategory = $this->jobCategory;
		} else {
			$message = $this->translator->translate('This category wasn\'t found.');
			$this->flashMessage($message, 'error');
			$this->redirect('default');
		}
	}

	/**
	 * @secured
	 * @resource('jobCategories')
	 * @privilege('delete')
	 */
	public function actionDelete($categoryId)
	{
		$this->jobCategory = $this->jobFacade->findJobCategory($categoryId);
		if ($this->jobCategory) {
			try {
				$this->jobFacade->deleteJobCategory($this->jobCategory);
				$message = $this->translator->translate('Category \'%category%\' was deleted.', ['category' => (string)$this->jobCategory]);
				$this->flashMessage($message, 'success');
			} catch (DBALException $exc) {
				$message = $this->translator->translate('\'%category%\' has child category or job. You can\'t delete it.', ['category' => (string)$this->jobCategory]);
				$this->flashMessage($message, 'danger');
			}
		} else {
			$message = 'Category was not found.';
			$this->flashMessage($message, 'danger');
		}
		$this->redirect('default');
	}

	public function createComponentJobCategoryForm()
	{
		$control = $this->jobCategoryFactory->create();
		$control->onAfterSave = function (JobCategory $saved) {
			$message = $this->translator->translate('\'%category%\' was successfully saved.', ['category' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('default');
		};
		return $control;
	}

	public function createComponentJobCategoryDataView()
	{
		$control = $this->jobCategoryDataViewFactory->create();
		$control->setJobCategories($this->jobFacade->findCategories());
		return $control;
	}
}
