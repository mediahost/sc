<?php

namespace App\Components\Job;
use Nette\Application\UI\Form;
use Nette\Utils\DateTime;

class NotesControl extends \App\Components\BaseControl
{
    /** @var array */
	public $onAfterSave = [];

    /** @var \App\Model\Entity\Job */
    private $job;


    public function render() {
        $this->template->job = $this->job;
        parent::render();
    }

    public function handleEdit() {
        $this->setTemplateFile('edit');
        $this->redrawControl('notesControl');
    }

    public function handlePreview() {
        $this->redrawControl('notesControl');
    }

    public function createComponentForm() {
        $form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new \App\Forms\Renderers\Bootstrap3FormRenderer());
        $form->getElementPrototype()->addClass('ajax');

		$form->addTextArea('notes')->setAttribute('rows', '8');
        $form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
    }

    public function formSucceeded(Form $form, $values) {
        $this->load($values);
		$this->save();
        $this->redrawControl('notesControl');
    }

    protected function getDefaults() {
        return [
            'notes' => $this->job->notes
        ];
    }

    private function save()
	{
		$cvRepo = $this->em->getRepository(\App\Model\Entity\Job::getClassName());
		$cvRepo->save($this->job);
	}

    protected function load($values) {
        $this->job->notes = $values->notes;
        $this->job->notes_updated = new DateTime();
		return $this;
    }

    public function setJob(\App\Model\Entity\Job $job) {
        $this->job = $job;
        return $this;
    }
}

interface INotesControlFactory
{

	/** @return NotesControl */
	function create();
}
