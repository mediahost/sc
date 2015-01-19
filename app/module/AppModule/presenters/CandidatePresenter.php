<?php

namespace App\AppModule\Presenters;

use App\Components\Candidate\ISkillsControlFactory;
use App\Components\Candidate\SkillsControl;
use App\Model\Entity\Candidate;
use App\Model\Entity\Skill;
use App\Model\Entity\User;
use App\TaggedString;

/**
 * 
 */
class CandidatePresenter extends BasePresenter
{
	// <editor-fold defaultstate="collapsed" desc="inject">

	/** @var ISkillsControlFactory @inject */
	public $iSkillsControlFactory;

	// </editor-fold>

	/**
	 * @secured
	 * @resource('candidate')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		$user = $this->em->getDao(User::getClassName())->find($this->user->id);
		if (!$user->candidate) {
			// create new candidate
			$candidate = new Candidate();
			$user->candidate = $candidate;
			$this->em->getDao(User::getClassName())->save($user);
		}

		$this->template->candidate = $user->candidate;
		$this->template->skills = $this->em->getDao(Skill::getClassName())->findAll();
	}

	/**
	 * @secured
	 * @resource('candidate')
	 * @privilege('default')
	 */
	public function actionSkills()
	{
		
	}

	// <editor-fold defaultstate="collapsed" desc="forms">

	/** @return SkillsControl */
	public function createComponentSkillsForm()
	{
		$control = $this->iSkillsControlFactory->create();
		$control->onAfterSave = function (Candidate $saved) {
			$message = new TaggedString('Candidate \'%s\' was successfully saved.', (string) $saved);
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		};
		return $control;
	}

	// </editor-fold>
}
