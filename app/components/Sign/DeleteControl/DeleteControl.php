<?php

Namespace App\Components;

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

	/**
	 * @return UI\Form
	 */
	protected function createComponentDeleteForm()
	{
		$form = new UI\Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new \App\Forms\Renderers\MetronicFormRenderer());

		$form->addSubmit('delete', 'Delete');

		$form->onSuccess[] = $this->deleteFormSucceeded;
		return $form;
	}

	public function deleteFormSucceeded(UI\Form $form, $values)
	{
		$this->userFacade->hardDelete($this->presenter->user->id);
		$this->presenter->user->logout();
		$this->presenter->redirect(":Front:Sign:In");
	}

}

interface IDeleteControlFactory
{

	/** @return DeleteControl */
	function create();
}
