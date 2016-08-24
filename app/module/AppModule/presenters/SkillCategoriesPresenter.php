<?php

namespace App\AppModule\Presenters;

use App\Components\Grids\Skill\ISkillCategoriesGridFactory;
use App\Components\Skills\ISkillCategoryControlFactory;
use App\Components\Skills\ISkillCategoryDataViewFactory;
use App\Components\Skills\SkillCategoryControl;
use App\Model\Entity\SkillCategory;
use Kdyby\Doctrine\DBALException;
use Kdyby\Doctrine\EntityDao;

class SkillCategoriesPresenter extends BasePresenter
{

	/** @var SkillCategory */
	private $skillCategory;

	// <editor-fold desc="constants & variables">

	/** @var ISkillCategoryControlFactory @inject */
	public $iSkillCategoryControlFactory;

	/** @var ISkillCategoryDataViewFactory	 @inject */
	public $skillCategoryDataViewFactory;

	/** @var EntityDao */
	private $skillCategoryDao;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('skillCategories')
	 * @privilege('default')
	 */
	public function renderDefault()
	{

	}

	/**
	 * @secured
	 * @resource('skillCategories')
	 * @privilege('add')
	 */
	public function actionAdd()
	{
		$this->skillCategory = new SkillCategory;
		$this['skillCategoryForm']->setSkillCategory($this->skillCategory);
		$this->setView('edit');
	}

	/**
	 * @secured
	 * @resource('skillCategories')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$this->skillCategory = $this->skillCategoryDao->find($id);
		if ($this->skillCategory) {
			$this['skillCategoryForm']->setSkillCategory($this->skillCategory);
		} else {
			$this->flashMessage('This category wasn\'t found.', 'error');
			$this->redirect('default');
		}
	}

	public function renderEdit()
	{
		$this->template->skillCategory = $this->skillCategory;
	}

	/**
	 * @secured
	 * @resource('skillCategories')
	 * @privilege('delete')
	 */
	public function actionDelete($id)
	{
		$this->skillCategory = $this->skillCategoryDao->find($id);
		if ($this->skillCategory) {
			try {
				$this->skillCategoryDao->delete($this->skillCategory);
				$message = $this->translator->translate('Category \'%category%\' was deleted.', ['category' => (string)$this->skillCategory]);
				$this->flashMessage($message, 'success');
			} catch (DBALException $exc) {
				$message = $this->translator->translate('\'%category%\' has child category or skill. You can\'t delete it.', ['category' => (string)$this->skillCategory]);
				$this->flashMessage($message, 'danger');
			}
		} else {
			$this->flashMessage('Category was not found.', 'danger');
		}
		$this->redirect('default');
	}

	// </editor-fold>
	// <editor-fold desc="forms">

	/** @return SkillCategoryControl */
	public function createComponentSkillCategoryForm()
	{
		$control = $this->iSkillCategoryControlFactory->create();
		$control->onAfterSave = function (SkillCategory $saved) {
			$message = $this->translator->translate('\'%category%\' was successfully saved.', ['category' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('default');
		};
		return $control;
	}

	/**
	 * @return SkillCategoryDataView
	 */
	public function createComponentSkillCategoryDataView()
	{
		$control = $this->skillCategoryDataViewFactory->create();
		$control->setSkillsCategories($this->skillCategoryDao->findAll());
		return $control;
	}
}
