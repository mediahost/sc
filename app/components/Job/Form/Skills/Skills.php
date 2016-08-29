<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Job;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillKnowRequest;
use App\Model\Entity\SkillLevel;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\SkillFacade;
use Kdyby\Doctrine\EmptyValueException;
use Nette\Utils\ArrayHash;

class Skills extends BaseControl
{
	const SKILL_MIN = 1;
	const SKILL_MAX = 5;
	const SKILL_STEP = 1;
	const YEARS_MIN = 0;
	const YEARS_MAX = 50;
	const YEARS_STEP = 1;

	/** @var Job */
	private $job;

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var SkillFacade @inject */
	public $skillFacade;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$defaultValues = $this->getDefaults();
		$skills = $this->em->getDao(Skill::getClassName())->findAll();
		$skillLevels = $this->em->getDao(SkillLevel::getClassName())->findPairsName();
		$ranges = $form->addContainer('skillRange');
		$yearRange = $form->addContainer('yearRange');
		reset($skillLevels);
		$levelFromId = key($skillLevels);
		end($skillLevels);
		$levelToId = key($skillLevels);

		foreach ($skills as $skill) {
			if (isset($defaultValues['skillRange'][$skill->id])) {
				$from = $defaultValues['skillRange'][$skill->id][0];
				$to = $defaultValues['skillRange'][$skill->id][1];
				$skil_range = sprintf('[%d,%d]', $from, $to);
			} else {
				$skil_range = sprintf('[%d,%d]', $levelFromId, $levelToId);
			}

			$ranges->addText($skill->id, $skill->name)
				->setAttribute('class', 'slider')
				->setAttribute('data-slider-min', $levelFromId)
				->setAttribute('data-slider-max', $levelToId)
				->setAttribute('data-slider-step', self::SKILL_STEP)
				->setAttribute('data-slider-value', $skil_range)
				->setAttribute('data-slider-id', 'slider-primary');

			$minYear = isset($defaultValues['skillMinYear'][$skill->id]) ?
				$defaultValues['skillMinYear'][$skill->id] : self::YEARS_MIN;
			$maxYear = isset($defaultValues['skillMaxYear'][$skill->id]) ?
				$defaultValues['skillMaxYear'][$skill->id] : self::YEARS_MAX;
			$year_range = sprintf('[%d,%d]', $minYear, $maxYear);

			$yearRange->addText($skill->id, $skill->name)
				->setAttribute('class', 'slider')
				->setAttribute('data-slider-min', self::YEARS_MIN)
				->setAttribute('data-slider-max', self::YEARS_MAX)
				->setAttribute('data-slider-step', self::YEARS_STEP)
				->setAttribute('data-slider-value', $year_range)
				->setAttribute('data-slider-id', 'slider-primary');
		}

		$form->addSubmit('save', 'Save');
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		try {
			$this->save();
			$this->onAfterSave($this->job);
		} catch (EmptyValueException $e) {
			$errorMessage = $this->translator->translate('Some mandatory field isn\'t filled. Please go into basic info form and complete all data.');
			$form->addError($errorMessage);
			$this->presenter->flashMessage($errorMessage, 'danger');
		}
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
				sscanf($values->skillRange->{$skill->id}, '%d,%d', $levelFromId, $levelToId);
				$newSkillRequest->setLevels($skilLevelDao->find($levelFromId), $skilLevelDao->find($levelToId));
			}
			sscanf($values->yearRange->{$skill->id}, '%d,%d', $yearMin, $yearMax);
			$newSkillRequest->setYears($yearMin, $yearMax);

			if ($newSkillRequest->isLevelsMatter()) {
				$this->job->skillRequest = $newSkillRequest;
			}
		}
		$this->job->removeOldSkillRequests();
		return $this;
	}

	private function save()
	{
		$jobRepo = $this->em->getRepository(Job::getClassName());
		$jobRepo->save($this->job);
		return $this;
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
			throw new JobException('Use setJob(\App\Model\Entity\Job) before render');
		}
	}

	// <editor-fold desc="setters & getters">

	public function setJob(Job $job)
	{
		$this->job = $job;
		return $this;
	}

	// </editor-fold>

	public function render()
	{
		$this->template->job = $this->job;
		parent::render();
	}

	public function handleEdit()
	{
		$this->template->skills = $this->em->getDao(Skill::getClassName())->findAll();
		$this->template->categories = $this->skillFacade->getTopCategories();
		$this->setTemplateFile('edit');
		$this->redrawControl('skillControl');
	}

	public function handlePreview()
	{
		$this->redrawControl('skillControl');
	}
}

interface ISkillsFactory
{

	/** @return Skills */
	function create();
}
