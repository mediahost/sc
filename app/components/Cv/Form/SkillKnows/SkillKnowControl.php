<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Model\Entity\Cv;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillKnow;
use App\Model\Entity\SkillLevel;
use Nette\Utils\ArrayHash;

class SkillKnowControl extends BaseControl
{
	/** @var SkillKnow */
	private $skillKnow;

	/** @var Skill */
	private $skill;

	/** @var Cv */
	private $cv;


	public function render()
	{
		$this->setTemplateFile('SkillKnowControl');
		$this->template->skill = $this->skill;
		$this->template->skillKnow = $this->skillKnow;
		parent::render();
	}

	public function createComponentForm()
	{
		$form = new Form();
		$form->onSuccess[] = $this->formSucceeded;

		$form->addHidden('skillLevel');

		$form->addText('skillYears')
			->setType('number')
			->setAttribute('min', 0);

		$form->addSubmit('reset', '')
			->getControlPrototype()->class = 'rating-delete';

		$form->addSubmit('all', '')
			->getControlPrototype()->class = 'rating-all';

		$form->setDefaults($this->getDefaults());
		return $form;
	}

	public function getDefaults()
	{
		$result = ['skillLevel' => 0];
		if ($this->skillKnow && $this->skillKnow->level) {
			if (SkillLevel::FIRST_PRIORITY <= $this->skillKnow->level->id && $this->skillKnow->level->id <= SkillLevel::LAST_PRIORITY) {
				$result['skillLevel'] = $this->skillKnow->level->id;
			} else {
				$result['skillLevel'] = SkillLevel::LAST_PRIORITY;
			}
			$result['skillYears'] = $this->skillKnow->level->id > 1 ? $this->skillKnow->years : 0;
		}
		return $result;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$this->redrawControl();
		if ($form['reset']->isSubmittedBy()) {
			$this->remove();
		} else if ($form['all']->isSubmittedBy()) {
			$this->addAll();
		} else {
			$this->load($values);
		}
		$this->save();
	}

	private function save()
	{
		$cvRepo = $this->em->getRepository(Cv::getClassName());
		$cvRepo->save($this->cv);
		return $this;
	}

	private function remove()
	{
		if ($this->skillKnow) {
			$this->cv->removeSkill($this->skillKnow);
			$this['form']['skillLevel']->value = SkillLevel::NONE;
			$this['form']['skillYears']->value = '';
		}
	}

	private function addAll()
	{
		$level = $this->em->getRepository(SkillLevel::getClassName())->find(SkillLevel::NOT_DEFINED);
		if ($level) {
			if (!$this->skillKnow) {
				$this->skillKnow = new SkillKnow();
				$this->skillKnow->skill = $this->skill;
				$this->skillKnow->years = 0;
				$this->skillKnow->cv = $this->cv;
			}
			$this->skillKnow->level = $level;
			$this->cv->setSkillKnow($this->skillKnow);
		}
		$this['form']['skillLevel']->value = SkillLevel::LAST_PRIORITY;
	}

	private function load(ArrayHash $values)
	{
		if (!$this->skillKnow) {
			$this->skillKnow = new SkillKnow();
		}

		$level = $this->em->getRepository(SkillLevel::getClassName())->find($values->skillLevel);
		$years = isset($values->skillYears) ? $values->skillYears : 0;

		$this->skillKnow->skill = $this->skill;
		$this->skillKnow->level = $level;
		$this->skillKnow->years = $years;
		$this->skillKnow->cv = $this->cv;
		$this->cv->skillKnow = $this->skillKnow;
		return $this;
	}

	public function setSkillKnow(SkillKnow $skillKnow = null)
	{
		$this->skillKnow = $skillKnow;
		return $this;
	}

	public function setSkill(Skill $skill)
	{
		$this->skill = $skill;
		return $this;
	}

	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}
}

interface ISkillKnowControlFactory
{
	/** @return SkillKnowControl */
	public function create();
}