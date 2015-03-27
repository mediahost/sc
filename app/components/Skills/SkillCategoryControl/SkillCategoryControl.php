<?php

namespace App\Components\Skills;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\SkillCategory;
use App\Model\Facade\RoleFacade;
use Exception;
use Nette\Utils\ArrayHash;

/**
 * Form with all user's personal settings.
 */
class SkillCategoryControl extends BaseControl
{

	/** @var SkillCategory */
	private $skillCategory;

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
		$parents = $skillCategoryDao->findPairs('name');
		if ($this->skillCategory) {
			unset($parents[$this->skillCategory->id]);
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
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->skillCategory);
	}

	private function load(ArrayHash $values)
	{
		$this->skillCategory->name = $values->name;
		$this->skillCategory->parent = NULL;
		if ($values->parent) {
			$skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
			$skillParent = $skillCategoryDao->find($values->parent);
			if ($skillParent) {
				$this->skillCategory->parent = $skillParent;
			}
		}
		return $this;
	}
	
	private function save()
	{
		$this->em->persist($this->skillCategory);
		$this->em->flush();
		return $this;
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [
			'name' => $this->skillCategory->name,
			'parent' => $this->skillCategory->parent ? $this->skillCategory->parent->id : NULL,
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->skillCategory) {
			throw new SkillCategoryControlException('Use setSkillCategory(\App\Model\Entity\SkillCategory) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setSkillCategory(SkillCategory $skillCategory)
	{
		$this->skillCategory = $skillCategory;
		return $this;
	}

	// </editor-fold>
}

class SkillCategoryControlException extends Exception
{
	
}

interface ISkillCategoryControlFactory
{

	/** @return SkillCategoryControl */
	function create();
}
