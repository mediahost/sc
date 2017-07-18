<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Model\Entity\Match;
use App\Model\Entity\Note;
use Nette\Utils\ArrayHash;

class MatchNotes extends BaseControl
{

	/** @var Match */
	private $match;

	/** @var string */
	private $type;

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
		if ($this->type === Note::TYPE_ADMIN) {
			$this->template->notes = $this->match->candidate->getAdminNotes();
			$this->template->editable = $this->user->isAllowed('adminNotes', 'add');
		} else if ($this->type === Note::TYPE_COMPANY) {
			$this->template->notes = $this->match->companyNotes;
			$this->template->editable = $this->user->isAllowed('companyNotes', 'add');
		} else if ($this->user->isAllowed('adminNotes', 'view')) {
			$this->template->notes = $this->match->candidate->getAdminNotes();
		} else if ($this->user->isAllowed('companyNotes', 'view')) {
			$this->template->notes = $this->match->companyNotes;
		}
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
		$this->onAfterSave($this->match);
	}

	protected function load(ArrayHash $values, Form $form)
	{
		$user = $this->user->getIdentity();
		$text = $values->message;
		if ($this->type === Note::TYPE_ADMIN && $this->user->isAllowed('adminNotes')) {
			$this->match->addAdminNote($user, $text, $values->noteId);
		} else if ($this->type === Note::TYPE_COMPANY && $this->user->isAllowed('companyNotes')) {
			$this->match->addCompanyNote($user, $text, $values->noteId);
		}

		return $this;
	}

	private function save()
	{
		$matchRepo = $this->em->getRepository(Match::getClassName());
		$matchRepo->save($this->match);
		return $this;
	}

	// <editor-fold desc="setters & getters">

	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	public function setMatch(Match $match)
	{
		$this->match = $match;
		return $this;
	}

	// </editor-fold>
}

interface IMatchNotesFactory
{

	/** @return MatchNotes */
	function create();
}
