<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Mail\Messages\IShareMessageFactory;
use Nette\Utils\ArrayHash;


/**
 * Description of sendEmail
 *
 */
class SendEmail extends BaseControl
{
	
	/** @var IShareMessageFactory @inject */
	public $shareMessageFactory;
	
	/** @var ICvDocumentFactory @inject */
	public $cvDocumentFaktory;
	
	/** @var Cv */
	private $cv;

	
	/**
	 * Seter for Cv entity
	 * @param Cv $cv
	 * @return \App\Components\Cv\CvDocument
	 */
	public function setCv(\App\Model\Entity\Cv $cv)
	{
		$this->cv = $cv;
		return $this;
	}

	protected function createComponentForm() 
	{
		$form = new Form();
		$form->getElementPrototype()->addClass('ajax');
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());
		
		$form->addText('email', 'Send to')
			->addRule(Form::EMAIL, 'Entered value is not email!');
		$form->addTextArea('message', 'E-mail text');
		
		$form->addSubmit('save', 'Send');
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}
	
	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$fileName = 'webtemp/'.time().".pdf";
		$this['cvDocument']->generatePdf($fileName);
		$message = $this->shareMessageFactory->create();
		$message->addTo($values->email);
		$message->setHtmlBody($values->message);
		$message->addAttachment($fileName);
		$message->send();
		unlink($fileName);
		$this->flashMessage('CV has been sent to your mail.');
		$this->presenter->payload->closePopup = true;
		$this->presenter->invalidateControl('sendEmail');
	}
	
	public function createComponentCvDocument()
	{
		$control = $this->cvDocumentFaktory->create();
		$control->setCv($this->cv);
		return $control;
	}
}

interface ISendEmailFactory
{
	/** @return SendEmail */
	function create();
}