<?php

namespace App\Components\Job;

use App\Forms\Renderers\MetronicHorizontalFormRenderer;
use App\Model\Facade\JobFacade;


class JobCategoryFilter extends \App\Components\BaseControl 
{
    /** @var array */
	public $onAfterSend = [];
    
    /** @var JobFacade @inject */
	public $jobFacade;
    
    /** @var array */
	private $categoryRequests = [];
    
    
    public function render() {
        $jsonJobCategories = [];
        $jobCategories = $this->jobFacade->findTopCategories();
        foreach ($jobCategories as $category) {
			$jsonJobCategories[] = $this->jobCategoryToLeaf($category);
		}
        $this->template->jobCategories = $this->jobFacade->findCategoriesPairs();
        $this->template->jsonJobCategories = $jsonJobCategories;
        $this->setTemplateFile('default');
        parent::render();
    }
    
    public function renderPreview() {
        $this->template->categories = $this->categoryRequests;
        $this->setTemplateFile('categoryFilterPreview');
        parent::render();
    }
    
    protected function createComponentForm() {
        $form = new \App\Forms\Form();
		$form->setRenderer(new MetronicHorizontalFormRenderer());
		$form->setTranslator($this->translator);
//        $form->getElementPrototype()->class('ajax');
        
        $categories = $this->jobFacade->findCategoriesPairs();
        $categoriesContainer = $form->addContainer('categories');
        foreach ($categories as $categoryId => $categoryName) {
			$categoriesContainer->addCheckbox($categoryId, $categoryName)
				->setAttribute('class', 'inCategoryTree');
		}

		$form->addSubmit('send', 'Filter')
			->setAttribute('class', 'btn btn-primary');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
    }
    
    public function formSucceeded(\App\Forms\Form $form, \Nette\Utils\ArrayHash $values) {
        $this->categoryRequests = [];
        $categoriesAll = $this->jobFacade->findCategoriesPairs();
        foreach ($values->categories as $categoryId => $checked) {
			if ($checked) {
				$this->categoryRequests[$categoryId] = $categoriesAll[$categoryId];
			}
		}
        $this->onAfterSend($this->categoryRequests);
    }
    
    public function setCategoryRequests($requests) {
        foreach($requests as $id=>$request) {
            $this->categoryRequests[$id] = $request;
        }
    }
    
    private function jobCategoryToLeaf(\App\Model\Entity\JobCategory $category)
	{
		$leaf = [
			'id' => $category->id,
			'text' => $category->name,
		];
		$children = [];
		foreach ($category->childs as $child) {
			$children[] = $this->jobCategoryToLeaf($child);
		}
        $leaf['state'] = [
			'selected' => key_exists($category->id, $this->categoryRequests),
		];
		$leaf['children'] = $children;
		return $leaf;
	}
}

interface IJobCategoryFilterFactory
{
    
	/** @return JobCategoryFilter */
	function create();
}

