<?php

namespace App\AppModule\Presenters;

use App\Components\Grids\Skill\ISkillCategoriesGridFactory;
use App\Components\Grids\Skill\SkillCategoriesGrid;
use App\Components\Skills\ISkillCategoryControlFactory;
use App\Components\Skills\SkillCategoryControl;
use App\Model\Entity\SkillCategory;
use App\TaggedString;
use Kdyby\Doctrine\DBALException;
use Kdyby\Doctrine\EntityDao;

class SkillCategoriesPresenter extends BasePresenter
{

	/** @var SkillCategory */
	private $skillCategory;

	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	/** @var ISkillCategoryControlFactory @inject */
	public $iSkillCategoryControlFactory;

	/** @var ISkillCategoriesGridFactory @inject */
	public $iSkillCategoriesGridFactory;

	/** @var EntityDao */
	private $skillCategoryDao;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
	}

	// <editor-fold defaultstate="collapsed" desc="actions & renderers">

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
				$message = new TaggedString('Category \'%s\' was deleted.', (string) $this->skillCategory);
				$this->flashMessage($message, 'success');
			} catch (DBALException $exc) {
				$message = new TaggedString('\'%s\' has child category or skill. You can\'t delete it.', (string) $this->skillCategory);
				$this->flashMessage($message, 'danger');
			}
		} else {
			$this->flashMessage('Category was not found.', 'danger');
		}
		$this->redirect('default');
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="forms">

	/** @return SkillCategoryControl */
	public function createComponentSkillCategoryForm()
	{
		$control = $this->iSkillCategoryControlFactory->create();
		$control->onAfterSave = function (SkillCategory $saved) {
			$message = new TaggedString('\'%s\' was successfully saved.', (string) $saved);
			$this->flashMessage($message, 'success');
			$this->redirect('default');
		};
		return $control;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="grids">

	/** @return SkillCategoriesGrid */
	public function createComponentSkillCategoriesGrid()
	{
		$control = $this->iSkillCategoriesGridFactory->create();
		return $control;
	}

	// </editor-fold>
}
