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
	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	/** @var Facade\UserFacade @inject */
	public $userFacade;

	// </editor-fold>

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

	// <editor-fold defaultstate="collapsed" desc="setters">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="getters">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="renderers">
	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="handlers">
	// </editor-fold>
}

interface IDeleteControlFactory
{

	/** @return DeleteControl */
	function create();
}
