<?php

namespace App\AppModule\Presenters;

use App\Components\Skills\ISkillControlFactory;
use App\Components\Skills\SkillControl;
use App\Model\Entity\Skill;
use App\TaggedString;
use Kdyby\Doctrine\EntityDao;

class SkillsPresenter extends BasePresenter
{
	
	/** @var Skill */
	private $skill;
	
	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	/** @var ISkillControlFactory @inject */
	public $iSkillControlFactory;

	/** @var EntityDao */
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
	 * @resource('skills')
	 * @privilege('default')
	 */
	public function renderDefault()
	{
		$this->template->skills = $this->skillDao->findAll();
	}

	/**
	 * @secured
	 * @resource('skills')
	 * @privilege('add')
	 */
	public function actionAdd()
	{
		$this->skill = new Skill;
		$this['skillForm']->setSkill($this->skill);
		$this->setView('edit');
	}

	/**
	 * @secured
	 * @resource('skills')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$this->skill = $this->skillDao->find($id);
		if ($this->skill) {
			$this['skillForm']->setSkill($this->skill);
		} else {
			$this->flashMessage('This skill wasn\'t found.', 'error');
			$this->redirect('default');
		}
	}
	
	public function renderEdit()
	{
		$this->template->skill = $this->skill;
	}

	/**
	 * @secured
	 * @resource('skills')
	 * @privilege('delete')
	 */
	public function actionDelete($id)
	{
		$this->skill = $this->skillDao->find($id);
		if ($this->skill) {
			$this->skillDao->delete($this->skill);
			$message = new TaggedString('\'%s\' was deleted.', $this->skill);
			$this->flashMessage($message, 'success');
		} else {
			$this->flashMessage('Skill was not found.', 'danger');
		}
		$this->redirect('default');
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="forms">

	/** @return SkillControl */
	public function createComponentSkillForm()
	{
		$control = $this->iSkillControlFactory->create();
		$control->onAfterSave = function (Skill $saved) {
			$message = new TaggedString('\'%s\' was successfully saved.', $saved->name);
			$this->flashMessage($message, 'success');
			$this->redirect('default');
		};
		return $control;
	}

	// </editor-fold>
}
