<?php

namespace App\Components\Job;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Model\Entity\Match;
use Nette\Utils\ArrayHash;

class AcceptReason extends BaseControl
{

	/** @var Match */
	private $match;

	/** @var bool */
	private $accept;

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var \Nette\Security\User @inject */
	public $user;

	// </editor-fold>

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

		$message = $form->addTextArea('message')
			->addRule(Form::FILLED, 'Must be filled', NULL, 3);
		if (!$this->accept) {
			$message->setAttribute('placeholder', $this->translator->translate('Type reason for rejection here'));
		}
		$message->getControlPrototype()->class = 'elastic form-control';

		$form->addSubmit('send', 'Send')
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
		$this->match->acceptReason = $values->message;
		return $this;
	}

	private function save()
	{
		$matchRepo = $this->em->getRepository(Match::getClassName());
		$matchRepo->save($this->match);
		return $this;
	}

	// <editor-fold desc="setters & getters">

	public function setMatch(Match $match)
	{
		$this->match = $match;
		return $this;
	}

	public function setAccept($value = TRUE)
	{
		$this->accept = $value;
		return $this;
	}

	// </editor-fold>
}

interface IAcceptReasonFactory
{

	/** @return AcceptReason */
	function create();
}
