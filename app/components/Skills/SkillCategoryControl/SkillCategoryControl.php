<?php

namespace App\Components\Skills;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use App\Model\Facade\RoleFacade;
use Kdyby\Doctrine\EntityManager;
use Nette\Utils\ArrayHash;

/**
 * Form with all user's personal settings.
 */
class SkillCategoryControl extends BaseControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var Skill */
	private $skill;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var EntityManager @inject */
	public $em;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addText('name', 'Name')
				->setRequired('Please fill name');

		// TODO: dont select own category in edit
		$skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
		$form->addSelect2('parent', 'Parent category', $skillCategoryDao->findPairs('name', 'id'))
				->setPrompt('--- NO PARENT ---');

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$entity = $this->load($values);
		$entityDao = $this->em->getDao(SkillCategory::getClassName());
		// TODO: Check on duplicity in skill category table
		$saved = $entityDao->save($entity);
		$this->onAfterSave($saved);
	}

	/**
	 * Load Entity from Form
	 * @param ArrayHash $values
	 * @return SkillCategory
	 */
	private function load(ArrayHash $values)
	{
		$entity = $this->getEntity();
		$entity->name = $values->name;

		$entity->parent = NULL;
		if ($values->parent) {
			$skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
			$skillParent = $skillCategoryDao->find($values->parent);
			if ($skillParent) {
				$entity->parent = $skillParent;
			}
		}

		return $entity;
	}

	/**
	 * Get Entity for Form
	 * @return array
	 */
	private function getDefaults()
	{
		$entity = $this->getEntity();
		$values = [
			'name' => $entity->name,
			'parent' => $entity->parent ? $entity->parent->id : NULL,
		];
		return $values;
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	/**
	 * @param SkillCategory $entity
	 * @return self
	 */
	public function setEntity(SkillCategory $entity)
	{
		$this->skill = $entity;
		return $this;
	}

	/**
	 * @return SkillCategory
	 */
	public function getEntity()
	{
		if ($this->skill) {
			return $this->skill;
		} else {
			return new SkillCategory;
		}
	}

	// </editor-fold>
}

interface ISkillCategoryControlFactory
{

	/** @return SkillCategoryControl */
	function create();
}
