<?php

namespace App\AppModule\Presenters;

use App\Model\Entity\Skill;

class SkillsPresenter extends BasePresenter
{
	
	// <editor-fold defaultstate="collapsed" desc="constants & variables">
	
	/** @var \App\Forms\SkillFormFactory @inject */
	public $skillFormFactory;
	
	/** @var Skill */
	protected $skill;
	
	/** @var \Kdyby\Doctrine\EntityDao */
	private $skillDao;
	
	// </editor-fold>
	
	protected function startup()
	{
		parent::startup();
		$this->skillDao = $this->em->getDao(Skill::getClassName());
	}
	
	// <editor-fold defaultstate="collapsed" desc="actions & renderers">
	/**
	 * @secured
	 * @resource('admin')
	 * @privilege('add')
	 */
	public function actionAdd()
	{
		$this->skill = new Skill;
		$this->skillFormFactory->setAdding();
		$this->setView("edit");
	}

	/**
	 * @secured
	 * @resource('admin')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$this->skill = $this->skillDao->find($id);
	}
	
	public function renderEdit()
	{
		$this->template->isAdd = TRUE;
	}
	
	// </editor-fold>
	
	// <editor-fold defaultstate="collapsed" desc="forms">
	
	public function createComponentSkillForm()
	{
		$form = $this->formFactoryFactory->create($this->skillFormFactory)
			->setEntity($this->skill)
			->create();
		$form->onSuccess[] = $this->skillFormSuccess;
		return $form;
	}
	
	public function skillFormSuccess($form)
	{
		if ($form['submitContinue']->submittedBy) {
			$this->skillDao->save($this->skill);
			$this->redirect("edit", $this->skill->getId());
		}
		$this->redirect("default");
	}
	
	// </editor-fold>
	
}
