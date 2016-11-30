<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Job;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillKnowRequest;
use App\Model\Entity\SkillLevel;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\SkillFacade;

class SkillsFilter extends BaseControl
{
	const SEPARATOR = ',';
	const SKILL_MIN = 1;
	const SKILL_MAX = 5;
	const SKILL_STEP = 1;
	const YEARS_MIN = 0;
	const YEARS_MAX = 10;
	const YEARS_STEP = 1;
	const YEARS_POSTFIX = '+';

	/** @var SkillKnowRequest[] */
	private $skillRequests = [];

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSend = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var SkillFacade @inject */
	public $skillFacade;

	/** @var SkillLevel[] */
	private $skillLevels = [];

	// </editor-fold>


	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());
		$form->getElementPrototype()->class = [
			!$this->isAjax ?: 'ajax',
			!$this->isSendOnChange ?: 'sendOnChange',
		];

		$skillRange = $form->addContainer('skillRange');
		$yearRange = $form->addContainer('yearRange');

		$skills = $this->em->getRepository(Skill::getClassName())->findAll();
		foreach ($skills as $skill) {
			$skillRange->addText($skill->id, $skill->name);
			$yearRange->addText($skill->id, $skill->name);
		}

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->onAfterSend($values);
	}

	protected function load($values)
	{
		$this->skillRequests = [];
		$skills = $this->em->getRepository(Skill::getClassName())->findAll();

		foreach ($skills as $skill) {
			$newSkillRequest = new SkillKnowRequest();
			$newSkillRequest->skill = $skill;

			if (isset($values->skillRange->{$skill->id})) {
				$levelFromId = $levelToId = '';
				sscanf($values->skillRange->{$skill->id}, '%d,%d', $levelFromId, $levelToId);
				$newSkillRequest->setLevels($this->getSkillLevel($levelFromId), $this->getSkillLevel($levelToId));
			}
			if (isset($values->yearRange->{$skill->id})) {
				$yearMin = $yearMax = '';
				sscanf($values->yearRange->{$skill->id}, '%d,%d', $yearMin, $yearMax);
				$newSkillRequest->setYears($yearMin, $yearMax);
			}
			if ($newSkillRequest->isLevelsMatter()) {
				$this->skillRequests[] = $newSkillRequest;
			}
		}

		return $this;
	}

	protected function setSliders()
	{
		$skills = $this->em->getRepository(Skill::getClassName())->findAll();

		foreach ($skills as $skill) {
			$default = $this->getDefault($skill->id);

			$this['form']['skillRange'][$skill->id]
				->setAttribute('class', 'slider')
				->setAttribute('data-slider-min', self::SKILL_MIN)
				->setAttribute('data-slider-max', self::SKILL_MAX)
				->setAttribute('data-slider-step', self::SKILL_STEP)
				->setAttribute('data-slider-value', $default['skillRange'])
				->setAttribute('data-slider-id', 'slider-primary');

			$this['form']['yearRange'][$skill->id]
				->setAttribute('class', 'slider')
				->setAttribute('data-slider-min', self::YEARS_MIN)
				->setAttribute('data-slider-max', self::YEARS_MAX)
				->setAttribute('data-slider-step', self::YEARS_STEP)
				->setAttribute('data-slider-value', $default['yearRange'])
				->setAttribute('data-slider-id', 'slider-primary')
				->setAttribute('data-postfix', self::YEARS_POSTFIX);
		}
	}

	protected function getDefault($skillId)
	{
		$result = [
			'skillRange' => sprintf('[%d%s%d]', self::SKILL_MIN, self::SEPARATOR, self::SKILL_MAX),
			'yearRange' => sprintf('[%d%s%d]', self::YEARS_MIN, self::SEPARATOR, self::YEARS_MAX)
		];
		foreach ($this->skillRequests as $skillRequest) {
			if ($skillRequest->skill->id == $skillId) {
				$result = [
					'skillRange' => sprintf('[%d%s%d]',  $skillRequest->levelFrom->id, self::SEPARATOR, $skillRequest->levelTo->id),
					'yearRange' => sprintf('[%d%s%d]', $skillRequest->yearsFrom, self::SEPARATOR, $skillRequest->yearsTo)
				];
				return $result;
			}
		}
		return $result;
	}

	// <editor-fold desc="setters & getters">

	public function setJob(Job $job)
	{
		$this->setSkillRequests($job->skillRequests);
		return $this;
	}

	public function setSkillRequests($values)
	{
		if (count($values)) {
			$this->load($values);
		}
		return $this->skillRequests;
	}

	private function getSkillLevel($levelId)
	{
		if (!$this->skillLevels) {
			$levels = $this->em->getRepository(SkillLevel::getClassName())->findAll();
			foreach ($levels as $level) {
				$this->skillLevels[$level->id] = $level;
			}
		}
		return $this->skillLevels[$levelId];
	}

	// </editor-fold>

	public function render()
	{
		$this->setSliders();
		$this->template->skills = $this->em->getDao(Skill::getClassName())->findAll();
		$this->template->categories = $this->skillFacade->getTopCategories();
		$this->template->skillLevels = $this->skillFacade->getSkillLevelNames();
		$this->setTemplateFile('default');
		parent::render();
	}

	public function renderPreview()
	{
		$this->template->skillRequests = $this->skillRequests;
		$this->setTemplateFile('SkillFilterPreview');
		parent::render();
	}

	public static function getDefaultRangeValue()
	{
		return self::SKILL_MIN . self::SEPARATOR . self::SKILL_MAX;
	}

	public static function getDefaultYearsValue()
	{
		return self::YEARS_MIN . self::SEPARATOR . self::YEARS_MAX;
	}

	public static function separateValues($values)
	{
		return preg_split('/' . preg_quote(self::SEPARATOR, '/') . '/', $values, 2, PREG_SPLIT_NO_EMPTY);
	}
}

interface ISkillsFilterFactory
{

	/** @return SkillsFilter */
	function create();
}
