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
			->getControlPrototype()->class = 'btn btn-default btn-xs btn-round mr5 mb10';

		$form->setDefaults($this->getDefaults());
		return $form;
	}

	public function getDefaults()
	{
		$result = ['skillLevel' => 0];
		if ($this->skillKnow && $this->skillKnow->level) {
			$result['skillLevel'] = $this->skillKnow->level->id;
			$result['skillYears'] = $this->skillKnow->level->id > 1 ? $this->skillKnow->years : 0;
		}
		return $result;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$this->redrawControl();
		if ($form['reset']->isSubmittedBy()) {
			$this->remove();
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
			$this['form']['skillLevel']->value = 0;
			$this['form']['skillYears']->value = '';
		}
	}

	private function load(ArrayHash $values)
	{
		if (!$this->skillKnow) {
			$this->skillKnow = new SkillKnow();
		}

		$level = $this->em->getDao(SkillLevel::getClassName())->find($values->skillLevel);
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