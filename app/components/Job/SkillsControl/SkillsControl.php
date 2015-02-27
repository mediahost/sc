<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Controls\TextInputBased\TouchSpin;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Job;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillKnowRequest;
use App\Model\Entity\SkillLevel;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\SkillFacade;
use Nette\Utils\ArrayHash;

/**
 * Job skills form
 */
class SkillsControl extends BaseControl
{

	/** @var Job */
	private $job;

	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var SkillFacade @inject */
	public $skillFacade;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer);

		$skills = $this->em->getDao(Skill::getClassName())->findAll();
		$skillLevels = $this->em->getDao(SkillLevel::getClassName())->findPairsName();
		$ranges = $form->addContainer('skillRange');
		$yearsMin = $form->addContainer('skillMinYear');
		$yearsMax = $form->addContainer('skillMaxYear');

		foreach ($skills as $skill) {
			$ranges->addRangeSlider($skill->id, $skill->name, $skillLevels)
					->setColor('danger')
					->setPips();
			$yearsMinItem = $yearsMin->addTouchSpin($skill->id, $skill->name)
					->setMin(0)->setMax(100)
					->setSize(TouchSpin::SIZE_S)
					->setDefaultValue(0);
			$yearsMaxItem = $yearsMax->addTouchSpin($skill->id, $skill->name)
					->setMin(0)->setMax(100)
					->setSize(TouchSpin::SIZE_S)
					->setDefaultValue(0);
			
			$yearsMinItem->setAttribute('data-connected-id', $skill->id)
					->getControlPrototype()
					->class('connectedNumber min', TRUE);
			$yearsMaxItem->setAttribute('data-connected-id', $skill->id)
					->getControlPrototype()
					->class('connectedNumber max', TRUE);
		}

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->em->persist($this->job);
		$this->em->flush();
		$this->onAfterSave($this->job);
	}

	protected function load(ArrayHash $values)
	{
		$skills = $this->em->getDao(Skill::getClassName())->findAll();
		$skilLevelDao = $this->em->getDao(SkillLevel::getClassName());

		foreach ($skills as $skill) {
			$newSkillRequest = new SkillKnowRequest();
			$newSkillRequest->skill = $skill;
			$newSkillRequest->job = $this->job;

			if (isset($values->skillRange->{$skill->id})) {
				$levelFromId = reset($values->skillRange->{$skill->id});
				$levelToId = end($values->skillRange->{$skill->id});
				$newSkillRequest->setLevels($skilLevelDao->find($levelFromId), $skilLevelDao->find($levelToId));
			}
			$newSkillRequest->setYears($values->skillMinYear->{$skill->id}, $values->skillMaxYear->{$skill->id});

			$this->job->skillRequest = $newSkillRequest;
		}
		$this->job->removeOldSkillRequests();
		
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [
			'skillRange' => [],
			'skillMinYear' => [],
			'skillMaxYear' => [],
		];
		foreach ($this->job->skillRequests as $skillRequest) {
			$values['skillRange'][$skillRequest->skill->id] = [
				$skillRequest->levelFrom->id,
				$skillRequest->levelTo->id,
			];
			$values['skillMinYear'][$skillRequest->skill->id] = $skillRequest->yearsFrom;
			$values['skillMaxYear'][$skillRequest->skill->id] = $skillRequest->yearsTo;
		}
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->job) {
			throw new JobControlException('Use setJob(\App\Model\Entity\Job) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setJob(Job $job)
	{
		$this->job = $job;
		return $this;
	}

	// </editor-fold>

	public function render()
	{
		$this->template->skills = $this->em->getDao(Skill::getClassName())->findAll();
		$this->template->categories = $this->skillFacade->getTopCategories();
		parent::render();
	}

}

interface ISkillsControlFactory
{

	/** @return SkillsControl */
	function create();
}
