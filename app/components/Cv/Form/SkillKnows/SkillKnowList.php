<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Model\Entity\Cv;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use Nette\Application\UI\Multiplier;

class SkillKnowList extends BaseControl
{
	/** @var callable[] */
	public $onAfterSave = [];

	/** @var ISkillKnowControlFactory @inject */
	public $skillKnowControlFactory;

	/** @var Cv */
	private $cv;

	/** @var SkillCategory[] */
	private $categories;



	public function render() {
		$this->setTemplateFile('SkillKnowList');
		$this->template->categories = $this->categories;
		parent::render();
	}

	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}

	public function setCategories($categories)
	{
		$this->categories = $categories;
		return $this;
	}

	public function createComponentSkill()
	{
		return new Multiplier(function($id) {
			$skill = $this->em->getRepository(Skill::getClassName())->findOneById($id);
			$skillKnow = $this->cv->getSkillKnow($skill);
			$control = $this->skillKnowControlFactory->create()
				->setSkillKnow($skillKnow)
				->setSkill($skill)
				->setCv($this->cv);
			return $control;
		});
	}
}

interface ISkillKnowListFactory
{
	/** @return SkillKnowList */
	public function create();
}