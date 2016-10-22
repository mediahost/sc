<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\User;
use Nette\Utils\ArrayHash;

class CompleteCv extends BaseControl
{

	// <editor-fold desc="events">

	/** @var array */
	public $onSuccess = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var \Nette\Security\User @inject */
	public $user;

	// </editor-fold>

	public function render()
	{
		$this->setTemplateFile('cv');
		parent::render();
	}

	protected function createComponentForm()
	{
		$form = new Form();
		$form->setRenderer(new Bootstrap3FormRenderer());
		$form->setTranslator($this->translator);

		$form->getElementPrototype()->setClass('dropzone dz-clickable dz-started');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$user = $this->user->getIdentity();
		$candidate = $user->getPerson()->getCandidate();

		$candidate->cvFile = $form->getHttpData()['file'];

		$userRepo = $this->em->getRepository(User::getClassName());
		$userRepo->save($user, $candidate);

		$this->onSuccess($this, $candidate);
	}

	public function onErrorHandler()
	{
		$this->presenter->flashMessage('Wrong file type!', 'error');
		$this->redrawControl();
	}
}

interface ICompleteCvFactory
{
	/** @return CompleteCv */
	public function create();
}