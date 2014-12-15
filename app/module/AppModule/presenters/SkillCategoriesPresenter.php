<?php

namespace App\AppModule\Presenters;

use App\Components\Skills\ISkillCategoryControlFactory;
use App\Components\Skills\SkillCategoryControl;
use App\Model\Entity\SkillCategory;
use App\TaggedString;
use Kdyby\Doctrine\DBALException;
use Kdyby\Doctrine\EntityDao;

class SkillCategoriesPresenter extends BasePresenter
{
	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	/** @var ISkillCategoryControlFactory @inject */
	public $iSkillCategoryControlFactory;

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
		$this->template->skillCategories = $this->skillCategoryDao->findAll();
	}

	/**
	 * @secured
	 * @resource('skillCategories')
	 * @privilege('add')
	 */
	public function actionAdd()
	{
		$this->setView('edit');
	}

	/**
	 * @secured
	 * @resource('skillCategories')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$entity = $this->skillCategoryDao->find($id);
		if ($entity) {
			$this['skillCategoryForm']->setEntity($entity);
		} else {
			$this->flashMessage('This category wasn\'t found.', 'error');
			$this->redirect('default');
		}
	}

	public function renderEdit()
	{
		$this->template->isAdd = !$this['skillCategoryForm']->isEntityExists();
	}

	/**
	 * @secured
	 * @resource('skillCategories')
	 * @privilege('delete')
	 */
	public function actionDelete($id)
	{
		$skillCategory = $this->skillCategoryDao->find($id);
		if ($skillCategory) {
			try {
				$this->skillCategoryDao->delete($skillCategory);
				$message = new TaggedString('Category \'%s\' was deleted.', $skillCategory->name);
				$this->flashMessage($message, 'success');
			} catch (DBALException $exc) {
				$message = new TaggedString('\'%s\' has child category or skill. You can\'t delete it.', $skillCategory->name);
				$this->flashMessage($message, 'warning');
			}
		} else {
			$this->flashMessage('Category was not found.', 'warning');
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
			$message = new TaggedString('\'%s\' was successfully saved.', $saved->name);
			$this->flashMessage($message, 'success');
			$this->redirect('default');
		};
		return $control;
	}

	// </editor-fold>
}
