<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Job;
use Nette\Application\UI\Form;

class Notes extends BaseControl
{
	/** @var array */
	public $onAfterSave = [];

	/** @var Job */
	private $job;


	public function render()
	{
		$this->template->job = $this->job;
		parent::render();
	}

	public function handleEdit()
	{
		$this->setTemplateFile('edit');
		$this->redrawControl('notes');
	}

	public function handlePreview()
	{
		$this->redrawControl('notes');
	}

	public function createComponentForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());
		$form->getElementPrototype()->addClass('ajax');

		$form->addTextArea('notes')->setAttribute('rows', '8');
		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->redrawControl('notes');
	}

	protected function getDefaults()
	{
		return [
			'notes' => $this->job->notes
		];
	}

	private function save()
	{
		$jobRepo = $this->em->getRepository(Job::getClassName());
		$jobRepo->save($this->job);
	}

	protected function load($values)
	{
		$this->job->notes = $values->notes;
		return $this;
	}

	public function setJob(Job $job)
	{
		$this->job = $job;
		return $this;
	}
}

interface INotesFactory
{

	/** @return Notes */
	function create();
}
