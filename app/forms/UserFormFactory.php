<?php

namespace App\Forms;

use Tracy\Debugger as Debug;

/**
 * UserFormFactory
 *
 * @author Petr Poupě
 */
class UserFormFactory extends FormFactory
{

	/** @var \App\Model\Facade\Roles */
	private $roleFacade;

	/** @var array */
	private $roles;

	public function __construct(IFormFactory $formFactory, \App\Model\Facade\Roles $roleFacade)
	{
		parent::__construct($formFactory);
		$this->roleFacade = $roleFacade;
	}

	private function getRoles()
	{
		if ($this->roles === NULL) {
			$this->roles = $this->roleFacade->findPairs("name");
		}
		return $this->roles;
	}

	public function create()
	{
		$form = $this->formFactory->create();
		$form->addText('username', 'Username')
				->setOption("description", "username must be e-mail")
				->addRule(Form::EMAIL, "Username must be e-mail")
				->addRule(Form::FILLED, "Username must be filled");
		$password = $form->addText('password', 'Password');
		if ($this->isAdding()) {
			$password->addRule(Form::FILLED, "Password must be filled");
		}
		$role = $form->addMultiSelect2('roles', 'Roles', $this->getRoles())
				->setRequired("Select any role");

		$defaultRole = $this->roleFacade->findByName("client");
		if ($defaultRole) {
			$role->setDefaultValue($defaultRole->getId());
		}

		$form->addSubmit('_submit', 'Save');
		$form->addSubmit('submitContinue', 'Save and continue edit');
		return $form;
	}

}
