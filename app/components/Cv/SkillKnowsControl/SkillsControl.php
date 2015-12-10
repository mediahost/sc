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
class SkillsControl extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var SkillFacade @inject */
	public $skillFacade;
	
	/** @var Cv */
	private $cv;
	
	
	/**
	 * Renders control
	 */
	public function render()
	{
		$this->template->skills = $this->em->getDao(Skill::getClassName())->findAll();
		$this->template->categories = $this->skillFacade->getTopCategories();
		parent::render();
	}
	
	public function handleInputChange($name, $value) {
		//TODO handle only one field
	}


	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form;
		$form->getElementPrototype()->class('ajax sendOnChange');
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer);

		$skills = $this->em->getDao(Skill::getClassName())->findAll();
		$skillLevels = $this->em->getDao(SkillLevel::getClassName())->findPairsName();
		$levels = $form->addContainer('skillLevel');
		$years = $form->addContainer('skillYear');

		foreach ($skills as $skill) {
			$levels->addHidden($skill->id);	
			$years->addText($skill->id);
		}

		$form->addSubmit('save', 'Save');
		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->invalidateControl();
	}

	private function load(ArrayHash $values)
	{
		foreach ($values->skillLevel as $skillId => $levelId) {
			if($levelId == '') {
				continue;
			}
			$skill = $this->em->getDao(Skill::getClassName())->find($skillId);
			$level = $this->em->getDao(SkillLevel::getClassName())->find($levelId);
			$years = isset($values->skillYear[$skillId]) ? $values->skillYear[$skillId] : 0;

			$newSkillKnow = new SkillKnow();
			$newSkillKnow->skill = $skill;
			$newSkillKnow->level = $level;
			$newSkillKnow->years = $years;
			$newSkillKnow->cv = $this->cv;
			$this->cv->skillKnow = $newSkillKnow;
		}
		$this->cv->removeOldSkillKnows();
		return $this;
	}

	private function save()
	{
		$cvRepo = $this->em->getRepository(Cv::getClassName());
		$cvRepo->save($this->cv);
		return $this;
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [
			'skillLevel' => [],
			'skillYear' => [],
		];
		foreach ($this->cv->skillKnows as $skillKnow) {
			$values['skillLevel'][$skillKnow->skill->id] = $skillKnow->level->id;
			$values['skillYear'][$skillKnow->skill->id] = $skillKnow->level->id > 1 ? $skillKnow->years : 0;
		}
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->cv) {
			throw new CvControlException('Use setCv(\App\Model\Entity\Cv) before render');
		}
	}

	// <editor-fold desc="setters & getters">

	public function setCv(Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}

	// </editor-fold>

	

}

interface ISkillsControlFactory
{

	/** @return SkillsControl */
	function create();
}
