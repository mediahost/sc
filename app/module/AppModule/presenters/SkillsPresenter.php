<?php

namespace App\AppModule\Presenters;

use App\Components\Skills\ISkillControlFactory;
use App\Components\Skills\SkillControl;
use App\Model\Entity\Skill;
use App\TaggedString;
use Kdyby\Doctrine\EntityDao;

class SkillsPresenter extends BasePresenter
{
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
		$this->setView('edit');
		$this->template->isAdd = TRUE;
	}

	/**
	 * @secured
	 * @resource('skills')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$entity = $this->skillDao->find($id);
		if ($entity) {
			$this['skillForm']->setEntity($entity);
		} else {
			$this->flashMessage('This skill wasn\'t found.', 'error');
			$this->redirect('default');
		}
	}

	/**
	 * @secured
	 * @resource('skills')
	 * @privilege('delete')
	 */
	public function actionDelete($id)
	{
		$skill = $this->skillDao->find($id);
		if ($skill) {
			$this->skillDao->delete($skill);
			$message = new TaggedString('\'%s\' was deleted.', $skill->name);
			$this->flashMessage($message, 'success');
		} else {
			$this->flashMessage('Skill was not found.', 'warning');
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
