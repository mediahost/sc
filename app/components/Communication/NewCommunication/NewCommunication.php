<?php

namespace App\Components;

use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Company;
use App\Model\Entity\Sender;
use App\Model\Entity\User;
use App\Model\Facade\CommunicationFacade;
use App\Model\Facade\UserFacade;
use App\Model\Repository\UserRepository;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Tracy\Debugger;

class NewCommunication extends BaseControl
{

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	/** @var Sender */
	public $sender;

	/** @var array */
	public $onSend = [];

	public function createComponentForm()
	{
		$recipients = $this->communicationFacade->getSenders($this->sender);

		$form = new Form();
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addSelect('recipient', 'Recipient', $recipients)
			->addRule(Form::FILLED, 'Select recipient');
		$form->addTextArea('message', 'Message', NULL, 5)
			->addRule(Form::FILLED, 'Insert message');

		$form->addSubmit('send', 'Send');

		$form->onSuccess[] = $this->processForm;
		return $form;
	}

	public function processForm(Form $form, $values)
	{
		$senderRepo = $this->em->getRepository(Sender::getClassName());
		$recipient = $senderRepo->find($values->recipient);
		if ($recipient) {
			$message = $values->message;
			$communication = $this->communicationFacade->sendMessage($this->sender, $recipient, $message);
			$this->onSend($communication);
		} else {
			$form['recipient']->addError($this->translator->translate('Recipient not found'));
		}
	}

	public function setSender(Sender $sender)
	{
		$this->sender = $sender;
	}
}

interface INewCommunicationFactory
{

	/** @return NewCommunication */
	public function create();

}