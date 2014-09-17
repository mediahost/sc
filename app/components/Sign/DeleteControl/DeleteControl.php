<?php

Namespace App\Components\Sign;

/* Nette */
use Nette\Application\UI;

/* Application */
use App\Components,
	App\Model\Facade;

/**
 * Forgotten control.
 */
class DeleteControl extends Components\BaseControl
{

	/** @var Facade\UserFacade @inject */
	public $userFacade;

	/** @var Facade\AuthFacade @inject */
	public $authFacade;

	/** @var \Nette\Mail\IMailer @inject */
	public $mailer;

	/** @var \App\Model\Storage\MessageStorage @inject */
	public $messages;

	/**
	 * @return UI\Form
	 */
	protected function createComponentDeleteForm()
	{
		$form = new UI\Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new \App\Forms\Renderers\MetronicFormRenderer());

		$form->addSubmit('confirm', 'Yes');
		$form->addSubmit('cancel', 'No')
				->setValidationScope(FALSE)
				->onClick[] = $this->deleteFormCanceled;

		$form->onSuccess[] = $this->deleteFormSucceeded;
		return $form;
	}

	public function deleteFormCanceled(\Nette\Forms\Controls\SubmitButton $button)
	{
		$this->presenter->redirect(":Admin:UserSettings:");
	}

	public function deleteFormSucceeded(UI\Form $form, $values)
	{
		if ($form['confirm']->isSubmittedBy()) {
			$this->userFacade->hardDelete($this->presenter->user->id);
			$this->presenter->user->logout();
			$this->presenter->redirect(":Front:Sign:In");
		}
	}

}

interface IDeleteControlFactory
{

	/** @return DeleteControl */
	function create();
}
