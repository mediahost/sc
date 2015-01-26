<?php

namespace App\Components\Skills;

use App\Components\EntityControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use App\Model\Facade\RoleFacade;
use Nette\Utils\ArrayHash;

/**
 * Form with all user's personal settings.
 * 
 * @method self setEntity(Skill $entity)
 * @method Skill getEntity()
 * @property Skill $entity
 */
class SkillControl extends EntityControl
{
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
		$entity = $this->load($values);
		$entityDao = $this->em->getDao(Skill::getClassName());
		// TODO: Check on duplicity in skill table
		$saved = $entityDao->save($entity);
		$this->onAfterSave($saved);
	}

	/**
	 * Load Entity from Form
	 * @param ArrayHash $values
	 * @return Skill
	 */
	protected function load(ArrayHash $values)
	{
		$entity = $this->getEntity();
		$entity->name = $values->name;

		$skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
		$skillCategory = $skillCategoryDao->find($values->category);
		if ($values->category && $skillCategory) {
			$entity->category = $skillCategory;
		}

		return $entity;
	}

	/**
	 * Get Entity for Form
	 * @return array
	 */
	protected function getDefaults()
	{
		$entity = $this->getEntity();
		$values = [
			'name' => $entity->name,
			'category' => $entity->category ? $entity->category->id : NULL,
		];
		return $values;
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	protected function checkEntityType($entity)
	{
		return $entity instanceof Skill;
	}

	/** @return Skill */
	protected function getNewEntity()
	{
		return new Skill;
	}

	// </editor-fold>
}

interface ISkillControlFactory
{

	/** @return SkillControl */
	function create();
}
