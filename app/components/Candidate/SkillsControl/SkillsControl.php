<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Forms\Controls\TextInputBased\TouchSpin;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Candidate;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillLevel;
use Kdyby\Doctrine\EntityManager;
use Nette\Utils\ArrayHash;

/**
 * Form with skills settings.
 */
class SkillsControl extends BaseControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var EntityManager @inject */
	public $em;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var Skill[] */
	private $skills = [];

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer);

		$skillLevels = $this->em->getDao(SkillLevel::getClassName())->findPairs([], 'name', [], 'id');
		$form->addSlider('select', 'Select', $skillLevels)
				->setColor('info')
				->setPips();
		$form->addTouchSpin('spinner', 'Spinner')
				->setMin(0)->setMax(100)
				->setButtonDownClass('btn red')
				->setButtonUpClass('btn green')
				->setSize(TouchSpin::SIZE_S);

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		\Tracy\Debugger::barDump($values);
		exit;
		$entity = $this->load($values);
//		$candidateDao = $this->em->getDao(\App\Model\Entity\Candidate::getClassName());
//		$savedEntity = $candidateDao->save($entity);
		$this->onAfterSave($entity);
	}

	/**
	 * Load Entity from Form
	 * @param ArrayHash $values
	 * @return Candidate
	 */
	protected function load(ArrayHash $values)
	{
		$entity = new Candidate;
		return $entity;
	}

	/**
	 * Get Entity for Form
	 * @return array
	 */
	protected function getDefaults()
	{
		$values = [
			'spinner' => 0,
			'select' => 2,
		];
		return $values;
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	/** @return self */
//	public function setCanEditInfo($value = TRUE)
//	{
//		$this->canEditInfo = $value;
//		return $this;
//	}
	// </editor-fold>
}

interface ISkillsControlFactory
{

	/** @return SkillsControl */
	function create();
}
