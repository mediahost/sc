<?php

Namespace App\Components;

use Nette\Security as NS,
	Nette\Application\UI\Control,
	Nette\Application\UI\Form,
	Nette,
	Model,
	GettextTranslator\Gettext as Translator;

/**
 * Sign in form control
 * @author Martin Šifra <me@martinsifra.cz>
 */
class SignInControl extends Control
{
	
	/** @var Translator */
	private $translator;
	
	
	public function __construct(Translator $translator)
	{
		parent::__construct();
		$this->translator = $translator;
	}
	
	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/SignInControl.latte');
		$template->render();
	}

	/**
	 * Sign in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new \App\Forms\Renderers\MetronicFormRenderer());

		$form->addText('username', 'Username')
//                ->setRequired('Please enter your username')
				->setAttribute('placeholder', 'Username');

		$form->addPassword('password', 'Password')
//                ->setRequired('Please enter your password')
				->setAttribute('placeholder', 'Password');

		$form->addCheckbox('remember', 'Remember me')
						->getLabelPrototype()->class = "checkbox";

		$form->addSubmit('_submit', 'Login');


		// call method signInFormSucceeded() on success
		$form->onSuccess[] = $this->signInFormSucceeded;
		return $form;
	}

	public function signInFormSucceeded(Form $form, $values)
	{
		if ($values->remember) {
			$this->presenter->getUser()->setExpiration('14 days', FALSE);
		} else {
			$this->presenter->getUser()->setExpiration('20 minutes', TRUE);
		}

		try {
			$this->presenter->user->login($values->username, $values->password);
//            $this->presenter->restoreRequest($this->presenter->backlink);
			$this->presenter->redirect(':Admin:Dashboard:');
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError('Incorrect login or password!');
		}
	}
}

interface ISignInControlFactory
{
	/** @return SignInControl */
	function create();
}
