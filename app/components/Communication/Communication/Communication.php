<?php

namespace App\Components;

use App\Model\Entity;
use App\Model\Entity\Sender;
use App\Model\Facade\CommunicationFacade;
use Nette\Application\UI\Form;

class Communication extends BaseControl
{

	const MESSAGES_PER_PAGE = 5;

	/** @var array */
	public $onSend = [];

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	/** @var Entity\Communication */
	protected $communication;

	/** @var Sender */
	protected $sender;

	/** @var int @persistent */
	public $count = self::MESSAGES_PER_PAGE;

	public function createComponentMessage()
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
			->setAttribute('placeholder', $this->translator->translate('Type a message here...'))
			->getControlPrototype()->class = 'elastic form-control';
		$form->addSubmit('send', 'Send message')
			->getControlPrototype()->class = 'btn btn-info mt10';

		$form->onSuccess[] = $this->processForm;
		return $form;
	}

	public function processForm(Form $form, $values)
	{
		$this->communicationFacade->sendMessage($this->sender, $this->communication, $values->message);
		$form['message']->setValue('');
		$this->onSend();
	}

	public function render()
	{
		$this->template->communication = $this->communication;
		$this->template->sender = $this->sender;
		$this->template->messageCount = $this->count;
		$this->template->messagesPerPage = self::MESSAGES_PER_PAGE;
		parent::render();
	}

	public function isViewer(Sender $sender)
	{
		return $this->sender->id === $sender->id;
	}

	public function handleNotifyChange($value = NULL)
	{
		// TODO: implement
		$this->redrawControl('notifyButtons');
	}

	public function setCommunication(Entity\Communication $communication)
	{
		$this->communication = $communication;
		return $this;
	}

	public function setSender(Sender $sender)
	{
		$this->sender = $sender;
		return $this;
	}

}

interface ICommunicationFactory
{

	/** @return Communication */
	public function create();
}
