<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Form;
use Nette\Utils\ArrayHash;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Job;

class Descriptions extends BaseControl
{
	/** @var Job */
	private $job;
	
	/** @var array */
	public $onAfterSave = [];
	
	
    public function render() {
        $this->template->job = $this->job;
        parent::render();
    }
    
    public function handleEditDescription() {
        $this->template->drawDescription = true;
        $this->setTemplateFile('edit');
        $this->redrawControl('descriptionControl');
    }
    
    public function handleEditSummary() {
        $this->template->drawSummary = true;
        $this->setTemplateFile('edit');
        $this->redrawControl('descriptionControl');
    }
    
    public function handlePreview() {
        $this->redrawControl('descriptionControl');
    }
    
	/** @return Form */
	protected function createComponentFormDescription()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		$form->addTextArea('description', 'Description')
			->setAttribute('placeholder', 'Job description')
			->setAttribute('id', 'jobDescription');
		
		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}
    
    public function createComponentFormSummary() {
        $this->checkEntityExistsBeforeRender();

		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer);
        
        
		$form->addTextArea('summary', 'Summary')
			->setAttribute('placeholder', 'Job summary')
			->setAttribute('id', 'jobSummary');
        
        $form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
    }
	
	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->job);
	}
	
	protected function load(ArrayHash $values)
	{
        if (isset($values->description)) {
            $this->job->description = $values->description;
        }
		if (isset($values->summary)) {
            $this->job->summary = $values->summary;
        }
		return $this;
	}
	
	private function save()
	{
		$cvRepo = $this->em->getRepository(Job::getClassName());
		$cvRepo->save($this->job);
	}

	protected function getDefaults()
	{
		$values = [
			'description' => $this->job->description,
			'summary' => $this->job->summary,
		];
		return $values;
	}
	
	private function checkEntityExistsBeforeRender()
	{
		if (!$this->job) {
			throw new JobException('Use setJob(\App\Model\Entity\Job) before render');
		}
	}
	
	public function setJob(Job $job)
	{
		$this->job = $job;
		return $this;
	}
}


Interface IDescriptionsFactory
{
	/** @return Descriptions */
	function create();
}