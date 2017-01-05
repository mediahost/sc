<?php

namespace App\Components\Conversation\Form;

use App\Components\BaseControl;
use App\Model\Entity\Communication;
use App\Model\Entity\Notification;
use App\Model\Entity\Sender;
use App\Model\Facade\CommunicationFacade;
use Nette\Application\UI\Form;
use Nette\Security\User;

class Conversation extends BaseControl
{

	const MESSAGES_PER_PAGE = 5;

	/** @var array */
	public $onSend = [];

	/** @var User @inject */
	public $identity;

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	/** @var Communication */
	protected $communication;

	/** @var Sender */
	protected $sender;

	/** @var bool */
	protected $disableEdit = FALSE;

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
			->getControlPrototype()->class = 'btn btn-primary mt10';

		$form->onSuccess[] = $this->processForm;
		return $form;
	}

	public function processForm(Form $form, $values)
	{
		$this->communicationFacade->sendMessage($this->communication, $this->sender, $values->message);
		$form['message']->setValue('');
		$this->onSend();
	}

	public function render()
	{
		$notifyRepo = $this->em->getRepository(Notification::getClassName());
		$notification = $notifyRepo->findOneBy([
			'communication' => $this->communication,
			'sender' => $this->sender,
		]);

		$this->template->editMode = !$this->disableEdit;
		$this->template->communication = $this->communication;
		$this->template->sender = $this->sender;
		$this->template->messageCount = $this->count;
		$this->template->messagesPerPage = self::MESSAGES_PER_PAGE;
		$this->template->notification = $notification ? $notification->enabled : NULL;
		$this->template->identity = $this->identity;
		parent::render();
	}

	public function isViewer(Sender $sender)
	{
		return $this->sender->id === $sender->id;
	}

	public function handleNotifyChange($value = NULL)
	{
		$notifyRepo = $this->em->getRepository(Notification::getClassName());
		$notification = $notifyRepo->findOneBy([
			'communication' => $this->communication,
			'sender' => $this->sender,
		]);

		if ($value === NULL) {
			if ($notification) {
				$notifyRepo->delete($notification);
			}
		} else {
			if (!$notification) {
				$notification = new Notification();
				$notification->communication = $this->communication;
				$notification->sender = $this->sender;
			}
			$notification->enabled = (bool)$value;
			$notifyRepo->save($notification);
		}
		$this->redrawControl('notifyButtons');
	}

	public function setCommunication(Communication $communication)
	{
		$this->communication = $communication;
		return $this;
	}

	public function setSender(Sender $sender)
	{
		$this->sender = $sender;
		return $this;
	}

	public function diableEdit($value = TRUE)
	{
		$this->disableEdit = $value;
		return $this;
	}

}

interface IConversationFactory
{

	/** @return Conversation */
	public function create();
}
