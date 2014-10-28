<?php

namespace App\AppModule\Presenters;

use App\Model\Entity\SkillCategory;

class SkillCategoriesPresenter extends BasePresenter
{
	
	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	
	/** @var \App\Forms\SkillCategoryFormFactory @inject */
	public $skillCategoryFormFactory;
	
	/** @var SkillCategory */
	protected $skillCategory;
	
	/** @var \Kdyby\Doctrine\EntityDao */
	private $skillCategoryDao;
	
	/** @var array */
	protected $skillCategories;
	
	// </editor-fold>
	
	protected function startup()
	{
		parent::startup();
		$this->skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
	}
	
	// <editor-fold defaultstate="collapsed" desc="actions & renderers">
	
	/**
	 * @secured
	 * @resource('admin')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		$this->skillCategories = $this->skillCategoryDao->findAll();
	}

	public function renderDefault()
	{
		$this->template->skillCategories = $this->skillCategories;
	}
	
	/**
	 * @secured
	 * @resource('admin')
	 * @privilege('add')
	 */
	public function actionAdd()
	{
		$this->skillCategory = new SkillCategory;
		$this->skillCategoryFormFactory->setAdding();
		$this->setView("edit");
	}

	/**
	 * @secured
	 * @resource('admin')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$this->skillCategory = $this->skillCategoryDao->find($id);
	}
	
	public function renderEdit()
	{
		$this->template->isAdd = TRUE;
	}
	
	/**
	 * @secured
	 * @resource('admin')
	 * @privilege('delete')
	 */
	public function actionDelete($id)
	{
		$this->skillCategory = $this->skillCategoryDao->find($id);
		if ($this->skillCategory) {
			$this->skillCategoryDao->delete($this->skillCategory);
			$this->flashMessage("Entity was deleted.", 'success');
		} else {
			$this->flashMessage("Entity was not found.", 'warning');
		}
		$this->redirect("default");
	}
	
	// </editor-fold>
	
	// <editor-fold defaultstate="collapsed" desc="forms">
	
	public function createComponentSkillCategoryForm()
	{
		$form = $this->formFactoryFactory->create($this->skillCategoryFormFactory)
			->setEntity($this->skillCategory)
			->create();
		$form->onSuccess[] = $this->skillCategoryFormSuccess;
		return $form;
	}
	
	public function skillCategoryFormSuccess($form)
	{
		if ($form['submitContinue']->submittedBy) {
			$this->skillCategoryDao->save($this->skillCategory);
			$this->redirect("edit", $this->skillCategory->getId());
		}
		$this->redirect("default");
	}
	
	// </editor-fold>
	
	
}
