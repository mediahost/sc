<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Forms\Form;
use App\Model\Entity;
use App\Model\Facade\JobFacade;
use Nette\Utils\ArrayHash;

class JobCategory extends BaseControl
{

	/** @var array */
	public $onAfterSave = [];

	/** @var JobFacade @inject */
	public $jobFacade;

	/** @var Entity\JobCategory */
	private $jobCategory;


	public function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		$form->addText('name', 'Name')->setRequired('Please fill name');

		$form->addSelect2('parent', 'Parent category', $this->getParentCategories())
			->setPrompt('--- NO PARENT ---');

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		if ($values->parent && $this->jobCategory) {
			$parentCategory = $this->jobFacade->findJobCategory($values->parent);
			if ($parentCategory && $this->jobFacade->isInParentTree($this->jobCategory, $parentCategory)) {
				$form['parent']->addError('Category can\'t be own parent');
				return;
			}
		}
		$this->load($values);
		$this->jobFacade->saveJobCategory($this->jobCategory);
		$this->onAfterSave($this->jobCategory);
	}

	private function load(ArrayHash $values)
	{
		$this->jobCategory->name = $values->name;
		$this->jobCategory->parent = NULL;
		if ($values->parent) {
			$this->jobCategory->parent = $this->jobFacade->findJobCategory($values->parent);
		}
		return $this;
	}

	protected function getDefaults()
	{
		$values = [
			'name' => $this->jobCategory->name,
			'parent' => $this->jobCategory->parent ? $this->jobCategory->parent->id : NULL,
		];
		return $values;
	}

	public function setJobCategory(Entity\JobCategory $jobCategory)
	{
		$this->jobCategory = $jobCategory;
		return $this;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->jobCategory) {
			throw new JobException('Use setJobCategory(\App\Model\Entity\JobCategory) before render');
		}
	}

	private function getParentCategories()
	{
		$parents = $this->jobFacade->findCategoriesPairs();
		if ($this->jobCategory) {
			unset($parents[$this->jobCategory->id]);
		}
		return $parents;
	}
}

interface IJobCategoryFactory
{

	/** @return JobCategory */
	function create();
}
