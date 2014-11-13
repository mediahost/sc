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
class SkillControl extends BaseControl
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
				->setRequired('Please fill non-enpty value');

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
		// TODO: Check on duplicity
		$saved = $entityDao->save($entity);
		$this->onAfterSave($saved);
	}

	/**
	 * Load Entity from Form
	 * @param ArrayHash $values
	 * @return Skill
	 */
	private function load(ArrayHash $values)
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
	private function getDefaults()
	{
		$entity = $this->getEntity();
		$values = [
			'name' => $entity->name,
			'category' => $entity->category ? $entity->category->id : NULL,
		];
		return $values;
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	/**
	 * @param Skill $entity
	 * @return self
	 */
	public function setEntity(Skill $entity)
	{
		$this->skill = $entity;
		return $this;
	}

	/**
	 * @return Skill
	 */
	public function getEntity()
	{
		if ($this->skill) {
			return $this->skill;
		} else {
			return new Skill;
		}
	}

	// </editor-fold>
}

interface ISkillControlFactory
{

	/** @return SkillControl */
	function create();
}
