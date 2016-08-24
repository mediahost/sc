<?php

namespace App\AppModule\Presenters;

use App\Components\Grids\Skill\ISkillsGridFactory;
use App\Components\Skills\ISkillControlFactory;
use App\Components\Skills\ISkillDataViewFactory;
use App\Components\Skills\SkillControl;
use App\Components\Skills\SkillDataView;
use App\Model\Entity\Skill;
use Kdyby\Doctrine\EntityDao;

class SkillsPresenter extends BasePresenter
{

	/** @var Skill */
	private $skill;

	// <editor-fold desc="constants & variables">

	/** @var ISkillControlFactory @inject */
	public $iSkillControlFactory;

	/** @var ISkillsGridFactory @inject */
	public $iSkillsGridFactory;

	/** @var ISkillDataViewFactory @inject */
	public $skillDataViewFactory;

	/** @var EntityDao */
	private $skillDao;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->skillDao = $this->em->getDao(Skill::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('skills')
	 * @privilege('default')
	 */
	public function renderDefault()
	{

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
			$message = $this->translator->translate('This skill wasn\'t found.');
			$this->flashMessage($message, 'error');
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
			$message = $this->translator->translate('\'%skill%\' was deleted.', ['skill' => $this->skill]);
			$this->flashMessage($message, 'success');
		} else {
			$message = $this->translator->translate('Skill was not found.');
			$this->flashMessage($message, 'danger');
		}
		$this->redirect('default');
	}

	// </editor-fold>
	// <editor-fold desc="forms">

	/** @return SkillControl */
	public function createComponentSkillForm()
	{
		$control = $this->iSkillControlFactory->create();
		$control->onAfterSave = function (Skill $saved) {
			$message = $this->translator->translate('\'%skill%\' was successfully saved.', ['skill' => $saved->name]);
			$this->flashMessage($message, 'success');
			$this->redirect('default');
		};
		return $control;
	}

	/** @return SkillDataView */
	public function createComponentSkillDataView()
	{
		$control = $this->skillDataViewFactory->create();
		$control->setSkills($this->skillDao->findAll());
		return $control;
	}
}
