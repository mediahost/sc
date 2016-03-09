<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Form;
use Nette\Utils\ArrayHash;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Job;

/**
 * Description of DescriptionsControl
 *
 */
class DescriptionsControl extends BaseControl
{
	/** @var Job */
	private $job;
	
	/** @var array */
	public $onAfterSave = [];
	
	
	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer);
		//$form->getElementPrototype()->addClass('ajax');

		$form->addTextArea('description', 'Description')
			->setAttribute('placeholder', 'Job description')
			->setAttribute('id', 'jobDescription');
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
		//$this->invalidateControl();
		$this->onAfterSave($this->job);
	}
	
	protected function load(ArrayHash $values)
	{
		$this->job->description = $values->description;
		$this->job->summary = $values->summary;
		return $this;
	}
	
	private function save()
	{
		$cvRepo = $this->em->getRepository(Job::getClassName());
		$cvRepo->save($this->job);
	}
	
	/** @return array */
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
			throw new JobControlException('Use setJob(\App\Model\Entity\Job) before render');
		}
	}
	
	public function setJob(Job $job)
	{
		$this->job = $job;
		return $this;
	}
}


Interface IDescriptionsControlFactory
{
	/** @return DescriptionsControl */
	function create();
}