<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\User;
use Nette\Utils\ArrayHash;

class CompleteCandidateFromCv extends BaseControl
{
	/** @var \Nette\Security\User @inject */
	public $user;

	public function render()
	{
		$this->setTemplateFile('candidateFromCv');
		$this->template->form = $this['form'];
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
		$file = $form->getHttpData()['file'];
		$userRepo = $this->em->getRepository(User::getClassName());
		$user = $this->user->getIdentity();
		$candidate = $user->getPerson()->getCandidate();
		$candidate->cvFile = $file;
		$userRepo->save($user, $candidate);
	}

	public function onErrorHandler()
	{
		$this->presenter->flashMessage('Wrong file type!', 'error');
		$this->redrawControl();
	}
}

interface ICompleteCandidateFromCvFactory
{
	/** @return CompleteCandidateFromCv */
	public function create();
}