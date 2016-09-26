<?php

namespace App\AppModule\Presenters;

use App\Components\Grids\Skill\ISkillCategoriesGridFactory;
use App\Components\Skills\ISkillCategoryFactory;
use App\Components\Skills\SkillCategory;
use App\Model\Entity;
use Kdyby\Doctrine\DBALException;
use Kdyby\Doctrine\EntityDao;

class SkillCategoriesPresenter extends BasePresenter
{

	/** @var Entity\SkillCategory */
	private $skillCategory;

	// <editor-fold desc="constants & variables">

	/** @var ISkillCategoryFactory @inject */
	public $iSkillCategoryFactory;

	/** @var ISkillCategoriesGridFactory @inject */
	public $iSkillCategoriesGridFactory;

	/** @var EntityDao */
	private $skillCategoryRepo;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->skillCategoryRepo = $this->em->getRepository(Entity\SkillCategory::getClassName());
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
		$this->skillCategory = new Entity\SkillCategory();
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
		$this->skillCategory = $this->skillCategoryRepo->find($id);
		if ($this->skillCategory) {
			$this['skillCategoryForm']->setSkillCategory($this->skillCategory);
		} else {
			$message = $this->translator->translate('This category wasn\'t found.');
			$this->flashMessage($message, 'error');
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
		$this->skillCategory = $this->skillCategoryRepo->find($id);
		if ($this->skillCategory) {
			try {
				$this->skillCategoryRepo->delete($this->skillCategory);
				$message = $this->translator->translate('Category \'%category%\' was deleted.', ['category' => (string)$this->skillCategory]);
				$this->flashMessage($message, 'success');
			} catch (DBALException $exc) {
				$message = $this->translator->translate('\'%category%\' has child category or skill. You can\'t delete it.', ['category' => (string)$this->skillCategory]);
				$this->flashMessage($message, 'danger');
			}
		} else {
			$message = $this->translator->translate('Category was not found.');
			$this->flashMessage($message, 'danger');
		}
		$this->redirect('default');
	}

	// </editor-fold>
	// <editor-fold desc="forms">

	/** @return SkillCategory */
	public function createComponentSkillCategoryForm()
	{
		$control = $this->iSkillCategoryFactory->create();
		$control->onAfterSave = function (Entity\SkillCategory $saved) {
			$message = $this->translator->translate('\'%category%\' was successfully saved.', ['category' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('default');
		};
		return $control;
	}

	/** @return SkillCategoriesGrid */
	public function createComponentSkillCategoriesGrid()
	{
		$control = $this->iSkillCategoriesGridFactory->create();
		return $control;
	}
}
