<?php

namespace App\Components\Skills;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\SkillFacade;
use Exception;
use Nette\Utils\ArrayHash;

class SkillCategory extends BaseControl
{

	/** @var Entity\SkillCategory */
	private $skillCategory;

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var RoleFacade @inject */
	public $roleFacade;
    
    /** @var SkillFacade @inject */
	public $skillFacade;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		$form->addText('name', 'Name')
				->setRequired('Please fill name');

		$skillCategoryDao = $this->em->getDao(Entity\SkillCategory::getClassName());
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
		if ($values->parent && $this->skillCategory) {
            $skillCategoryDao = $this->em->getDao(Entity\SkillCategory::getClassName());
			$parentCategory = $skillCategoryDao->find($values->parent);
            if ($parentCategory  && $this->skillFacade->isInParentTree($this->skillCategory, $parentCategory)) {
                $form['parent']->addError('Category can\'t be own parent');
                return;
            }
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
			$skillCategoryDao = $this->em->getDao(Entity\SkillCategory::getClassName());
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
			throw new SkillCategoryException('Use setSkillCategory(\App\Model\Entity\SkillCategory) before render');
		}
	}

	// <editor-fold desc="setters & getters">

	public function setSkillCategory(Entity\SkillCategory $skillCategory)
	{
		$this->skillCategory = $skillCategory;
		return $this;
	}

	// </editor-fold>
}

class SkillCategoryException extends Exception
{

}

interface ISkillCategoryFactory
{

	/** @return SkillCategory */
	function create();
}
