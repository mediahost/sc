<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Controls\TextInputBased\TouchSpin;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Cv;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillKnow;
use App\Model\Entity\SkillLevel;
use App\Model\Facade\SkillFacade;
use Nette\Utils\ArrayHash;

/**
 * Form with skills settings.
 */
class SkillKnowsControl extends BaseControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var SkillFacade @inject */
	public $skillFacade;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var Cv */
	private $cv;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer);

		$skills = $this->em->getDao(Skill::getClassName())->findAll();
		$skillLevels = $this->em->getDao(SkillLevel::getClassName())->findPairs([], 'name', [], 'id');
		$levels = $form->addContainer('skillLevel');
		$years = $form->addContainer('skillYear');
		foreach ($skills as $skill) {
			$levels->addSlider($skill->id, $skill->name, $skillLevels)
					->setColor('success')
					->setTooltipFixed();
			$years->addTouchSpin($skill->id, $skill->name)
					->setMin(0)->setMax(100)
					->setSize(TouchSpin::SIZE_S)
					->setDefaultValue(0);
		}

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$entity = $this->load($values);
		$this->em->persist($entity);
		$this->em->flush();
		$this->onAfterSave($entity);
	}

	/**
	 * Load Entity from Form
	 * @param ArrayHash $values
	 * @return Cv
	 */
	protected function load(ArrayHash $values)
	{
		$entity = $this->getCv();
		$usedSkillIds = [];
		foreach ($values->skillLevel as $skillId => $levelId) {
			$skill = $this->em->getDao(Skill::getClassName())->find($skillId);
			$level = $this->em->getDao(SkillLevel::getClassName())->find($levelId);
			$years = isset($values->skillYear[$skillId]) ? $values->skillYear[$skillId] : 0;

			$newSkillKnow = new SkillKnow();
			$newSkillKnow->skill = $skill;
			$newSkillKnow->level = $level;
			$newSkillKnow->years = $years;
			$newSkillKnow->cv = $entity;
			$entity->skillKnow = $newSkillKnow;
			$usedSkillIds[] = $skillId;
		}
		return $entity;
	}

	/**
	 * Get Entity for Form
	 * @return array
	 */
	protected function getDefaults()
	{
		$values = [
			'skillLevel' => [],
			'skillYear' => [],
		];
		foreach ($this->getCv()->skillKnows as $skillKnow) {
			$values['skillLevel'][$skillKnow->skill->id] = $skillKnow->level->id;
			$values['skillYear'][$skillKnow->skill->id] = $skillKnow->level->id > 1 ? $skillKnow->years : 0;
		}
		return $values;
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	/** @return self */
	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}

	/** @return Cv */
	private function getCv()
	{
		if (!$this->cv) {
			throw new CvControlException('Must use method setCv(\App\Model\Entity\Cv)');
		}
		return $this->cv;
	}

	// </editor-fold>

	public function render()
	{
		$this->template->skills = $this->em->getDao(Skill::getClassName())->findAll();
		$this->template->categories = $this->skillFacade->getTopCategories();
		parent::render();
	}

}

interface ISkillKnowsControlFactory
{

	/** @return SkillKnowsControl */
	function create();
}
