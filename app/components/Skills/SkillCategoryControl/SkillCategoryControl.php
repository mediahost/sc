<?php

namespace App\Components\Skills;

use App\Components\EntityControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\SkillCategory;
use App\Model\Facade\RoleFacade;
use Kdyby\Doctrine\EntityManager;
use Nette\Utils\ArrayHash;

/**
 * Form with all user's personal settings.
 * 
 * @method self setEntity(SkillCategory $entity)
 * @method SkillCategory getEntity()
 * @property SkillCategory $entity
 */
class SkillCategoryControl extends EntityControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

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

		$skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
		$parents = $skillCategoryDao->findPairs('name', 'id');
		if ($this->entity) {
			unset($parents[$this->entity->id]);
		}
		$form->addSelect2('parent', 'Parent category', $parents)
				->setPrompt('--- NO PARENT ---');

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		if ($values->parent && $this->entity && $values->parent == $this->entity->id) {
			$form['parent']->addError('Category can\'t be own parent');
			return;
		}
		
		$entity = $this->load($values);
		$entityDao = $this->em->getDao(SkillCategory::getClassName());

		$saved = $entityDao->save($entity);
		$this->onAfterSave($saved);
	}

	/**
	 * Load Entity from Form
	 * @param ArrayHash $values
	 * @return SkillCategory
	 */
	protected function load(ArrayHash $values)
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
	protected function getDefaults()
	{
		$entity = $this->getEntity();
		$values = [
			'name' => $entity->name,
			'parent' => $entity->parent ? $entity->parent->id : NULL,
		];
		return $values;
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	protected function checkEntityType($entity)
	{
		return $entity instanceof SkillCategory;
	}

	/** @return SkillCategory */
	protected function getNewEntity()
	{
		return new SkillCategory;
	}

	// </editor-fold>
}

interface ISkillCategoryControlFactory
{

	/** @return SkillCategoryControl */
	function create();
}
