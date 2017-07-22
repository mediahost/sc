<?php

namespace App\Components\Candidate;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Model\Entity\Candidate;
use Nette\Utils\ArrayHash;

class CandidateNotes extends BaseControl
{
	/** @var Candidate */
	private $candidate;

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var \Nette\Security\User @inject */
	public $user;

	// </editor-fold>

	public function render()
	{
		$this->template->editable = FALSE;
		$this->template->notes = $this->candidate->getAdminNotes();
		$this->template->editable = $this->user->isAllowed('adminNotes', 'add');
		parent::render();
	}

	protected function createComponentForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		if ($this->isAjax) {
			$form->getElementPrototype()->class[] = 'ajax';
		}
		if ($this->isSendOnChange) {
			$form->getElementPrototype()->class[] = 'sendOnChange';
		}

		$form->addTextArea('message')
			->addRule(Form::FILLED, 'Must be filled', NULL, 3)
			->setAttribute('placeholder', $this->translator->translate('Type a note here...'))
			->getControlPrototype()->class = 'elastic form-control';

		$form->addHidden('noteId');

		$form->addSubmit('send', 'Save')
			->getControlPrototype()->class = 'btn btn-primary mt10';

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values, $form);
		$this->save();
		$this->onAfterSave($this->candidate);
	}

	protected function load(ArrayHash $values, Form $form)
	{
		$user = $this->user->getIdentity();
		$text = $values->message;
		if ($this->user->isAllowed('adminNotes')) {
			$this->candidate->addAdminNote($user, $text, $values->noteId);
		}

		return $this;
	}

	private function save()
	{
		$candidateRepo = $this->em->getRepository(Candidate::getClassName());
		$candidateRepo->save($this->candidate);
		return $this;
	}

	// <editor-fold desc="setters & getters">

	public function setCandidate($candidate)
	{
		$this->candidate = $candidate;
		return $this;
	}


	// </editor-fold>
}

interface ICandidateNotesFactory
{

	/** @return CandidateNotes */
	function create();
}
