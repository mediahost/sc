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


	protected function createComponentForm() 
	{
		$form = new Form();
		$form->getElementPrototype()->addClass('ajax');
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());
		
		$form->addText('email', 'Send to')
			->addRule(Form::EMAIL, 'Entered value is not email!');
		$form->addTextArea('message', 'E-mail text');
		
		$form->addSubmit('save', 'Save');
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}
	
	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$message = $this->shareMessageFactory->create();
		$message->addTo($values->email);
		$message->setHtmlBody($values->message);
		$message->send();
		$this->flashMessage('CV has been sent to your mail.');
	}
}

interface ISendEmailFactory
{
	/** @return SendEmail */
	function create();
}