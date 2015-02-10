<?php

namespace App\Components\Skills;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use App\Model\Facade\RoleFacade;
use Exception;
use Nette\Utils\ArrayHash;

/**
 * Form with all user's personal settings.
 */
class SkillControl extends BaseControl
{

	/** @var Skill */
	private $skill;

	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var RoleFacade @inject */
	public $roleFacade;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addText('name', 'Name')
				->setRequired('Please fill name');

		$skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
		$form->addSelect2('category', 'Skill category', $skillCategoryDao->findPairs('name', 'id'))
				->setRequired('Please select some category');

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$entityDao = $this->em->getDao(Skill::getClassName());
		// TODO: Check on duplicity in skill table
		$entityDao->save($this->skill);
		$this->onAfterSave($this->skill);
	}

	protected function load(ArrayHash $values)
	{
		$this->skill->name = $values->name;

		$skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
		$skillCategory = $skillCategoryDao->find($values->category);
		if ($values->category && $skillCategory) {
			$this->skill->category = $skillCategory;
		}
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [
			'name' => $this->skill->name,
			'category' => $this->skill->category ? $this->skill->category->id : NULL,
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->skill) {
			throw new SkillControlException('Use setSkill(\App\Model\Entity\Skill) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setSkill(Skill $skill)
	{
		$this->skill = $skill;
		return $this;
	}

	// </editor-fold>
}

class SkillControlException extends Exception
{
	
}

interface ISkillControlFactory
{

	/** @return SkillControl */
	function create();
}
