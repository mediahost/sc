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
use Nette\Utils\ArrayHash;

class SkillsFilter extends BaseControl
{
	const SKILL_MIN = 1;
	const SKILL_MAX = 5;
	const SKILL_STEP = 1;
	const YEARS_MIN = 0;
	const YEARS_MAX = 50;
	const YEARS_STEP = 1;

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

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$defaultValues = $this->getDefaults();
		$skills = $this->em->getRepository(Skill::getClassName())->findAll();
		$skillLevels = $this->em->getRepository(SkillLevel::getClassName())->findPairsName();
		reset($skillLevels);
		$levelFromId = key($skillLevels);
		end($skillLevels);
		$levelToId = key($skillLevels);

		$ranges = $form->addContainer('skillRange');
		$yearRange = $form->addContainer('yearRange');
		
		foreach ($skills as $skill) {
			if(isset($defaultValues['skillRange'][$skill->id])) {
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

		$form->addSubmit('send', 'Filter');
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->onAfterSend($this->skillRequests);
	}

	protected function load(ArrayHash $values)
	{
		$skills = $this->em->getRepository(Skill::getClassName())->findAll();
		$skilLevelRepo = $this->em->getRepository(SkillLevel::getClassName());

		foreach ($skills as $skill) {
			$newSkillRequest = new SkillKnowRequest();
			$newSkillRequest->skill = $skill;

			if (isset($values->skillRange->{$skill->id})) {
				sscanf($values->skillRange->{$skill->id}, '%d,%d', $levelFromId, $levelToId);
				$newSkillRequest->setLevels($skilLevelRepo->find($levelFromId), $skilLevelRepo->find($levelToId));
			}
			sscanf($values->yearRange->{$skill->id}, '%d,%d', $yearMin, $yearMax);
			$newSkillRequest->setYears($yearMin, $yearMax);

			if ($newSkillRequest->isLevelsMatter()) {
				$this->skillRequests[] = $newSkillRequest;
			}
		}
		
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
		foreach ($this->skillRequests as $skillRequest) {
			$values['skillRange'][$skillRequest->skill->id] = [
				$skillRequest->levelFrom->id,
				$skillRequest->levelTo->id,
			];
			$values['skillMinYear'][$skillRequest->skill->id] = $skillRequest->yearsFrom;
			$values['skillMaxYear'][$skillRequest->skill->id] = $skillRequest->yearsTo;
		}
		return $values;
	}

	// <editor-fold desc="setters & getters">

	public function setJob(Job $job)
	{
		$this->skillRequests = $job->skillRequests;
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

interface ISkillsFilterFactory
{

	/** @return SkillsFilter */
	function create();
}
