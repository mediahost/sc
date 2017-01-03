<?php

namespace App\Components\Conversation\Form;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Communication;
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

		$form->addMultiSelect2('recipients', 'Recipients', $this->getRecipients())
			->addRule(Form::FILLED, 'Select recipient');
		$form->addText('subject', 'Subject')
			->addRule(Form::FILLED, 'Fill subject');
		$form->addTextArea('message', 'Message', NULL, 5)
			->addRule(Form::FILLED, 'Insert message');

		$form->addSubmit('send', 'Send');

		$form->onSuccess[] = $this->processForm;
		return $form;
	}

	public function processForm(Form $form, $values)
	{
		$senderRepo = $this->em->getRepository(Sender::getClassName());
		$recipients = [];
		foreach ($values->recipients as $recipientId) {
			$recipients[] = $senderRepo->find($recipientId);
		}
		if (count($recipients)) {
			$communication = $this->communicationFacade->findOrCreate($this->sender, $recipients, $values->subject);
			$this->communicationFacade->sendMessage($communication, $this->sender, $values->message);
			$this->onSend($communication);
		} else {
			$form['recipients']->addError($this->translator->translate('Recipients not found'));
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