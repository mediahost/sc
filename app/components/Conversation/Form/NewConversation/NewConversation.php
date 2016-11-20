<?php

namespace App\Components\Conversation\Form;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Sender;
use App\Model\Facade\CommunicationFacade;
use App\Model\Facade\CompanyFacade;

class NewConversation extends BaseControl
{

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var Sender */
	public $sender;

	/** @var array */
	public $onSend = [];

	public function createComponentForm()
	{
		$form = new Form();
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addSelect2('recipient', 'Recipient', $this->getRecipients())
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

	private function getRecipients()
	{
		if ($this->sender->isCompany) {
			$recipients = [];
			$candidates = $this->companyFacade->findMatchedCandidates($this->sender->company);
			if (count($candidates)) {
				$recipients = $this->communicationFacade->getSendersFromCandidates($candidates);
			}
		} else {
			$recipients = $this->communicationFacade->getSenders($this->sender);
		}
		return $recipients;
	}
}

interface INewConversationFactory
{

	/** @return NewConversation */
	public function create();

}