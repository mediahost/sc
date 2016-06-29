<?php

namespace App\Components\Job;
use Exception;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Forms\Form;
use App\Model\Entity\JobCategory;
use App\Model\Facade\JobFacade;

class JobCategoryControl extends \App\Components\BaseControl {
    
    /** @var array */
	public $onAfterSave = [];
    
    /** @var JobFacade @inject */
    public $jobFacade;
    
    /** @var \App\Model\Entity\JobCategory */
    private $jobCategory;
    
    
    public function createComponentForm() 
    {
        $this->checkEntityExistsBeforeRender();
        $form = new Form;
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
    
    /**
     * @param Form $form
     * @param \Nette\Utils\ArrayHash $values
     */
    public function formSucceeded(Form $form, $values)
	{
		if ($values->parent && $this->jobCategory && $values->parent == $this->jobCategory->id) {
			$form['parent']->addError('Category can\'t be own parent');
			return;
		}
		$this->load($values);
		$this->jobFacade->saveJobCategory($this->jobCategory);
		$this->onAfterSave($this->jobCategory);
	}
    
    /**
     * @param \Nette\Utils\ArrayHash $values
     * @return \App\Components\Job\JobCategoryControl
     */
    private function load(\Nette\Utils\ArrayHash $values)
	{
		$this->jobCategory->name = $values->name;
		$this->jobCategory->parent = NULL;
		if ($values->parent) {
            $this->jobCategory->parent = $this->jobFacade->findJobCategory($values->parent);
		}
		return $this;
	}
    
    /**
     * @return array
     */
	protected function getDefaults()
	{
		$values = [
			'name' => $this->jobCategory->name,
			'parent' => $this->jobCategory->parent ? $this->jobCategory->parent->id : NULL,
		];
		return $values;
	}

    /**
     * Setter for $jobCategory
     * @param JobCategory $jobCategory
     * @return \App\Components\Job\JobCategoryControl
     */
    public function setJobCategory(JobCategory $jobCategory)
	{
		$this->jobCategory = $jobCategory;
		return $this;
	}
    
    private function checkEntityExistsBeforeRender()
	{
		if (!$this->jobCategory) {
			throw new JobCategoryControlException('Use setJobCategory(\App\Model\Entity\JobCategory) before render');
		}
	}
    
    private function getParentCategories() {
        $parents = $this->jobFacade->findCategoriesPairs();
        if ($this->jobCategory) {
			unset($parents[$this->jobCategory->id]);
		}
        return $parents;
    }
}

class JobCategoryControlException extends Exception
{

}

interface IJobCategoryControlFactory
{

	/** @return JobCategoryControl */
	function create();
}
