<?php

namespace App\Components\Sign;

use Nette\Application\UI\Control,
	Nette\Application\UI\Form,
	Nette,
	App\Model\Entity;

/**
 *
 * @author Martin Šifra <me@martinsifra.cz>
 */
class MergeControl extends Control
{

	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;

	/** @var \App\Model\Storage\RegistrationStorage */
	private $registrationStorage;

	public function __construct(\Kdyby\Doctrine\EntityManager $em, \App\Model\Storage\RegistrationStorage $reg)
	{
		$this->em = $em;
		$this->registrationStorage = $reg;
	}

	public function render()
	{
		$template = $this->template;
		$template->bool = $this->registrationStorage->isOauth();
		$template->setFile(__DIR__ . '/render.latte');
		$template->render();
	}

	/**
	 * Sign in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentMergeForm()
	{
		$form = new Form();
		$form->setRenderer(new \App\Forms\Renderers\MetronicFormRenderer());

		$form->addSubmit('yes', 'Yes');
		$form->addSubmit('no', 'No');


		// call method signInFormSucceeded() on success
		$form->onSuccess[] = $this->registerFormSucceeded;
		return $form;
	}

	public function registerFormSucceeded(Form $form, $values)
	{

		$this->presenter->redirect(':Admin:Dashboard:', ['merge' => NULL]);
	}

}

interface IMergeControlFactory
{

	/** @return MergeControl */
	function create();
}
