<?php

namespace App\Forms;

use Tracy\Debugger as Debug;
use Kdyby\Doctrine\EntityManager,
	Kdyby\Doctrine\EntityDao;

use App\Model\Entity\Role,
	App\Model\Facade\Users as UserFacade;

/**
 * UserFormFactory
 *
 * @author Petr Poupě
 */
class UserFormFactory extends FormFactory
{

	/** @var \App\Model\Facade\Roles */
	private $roleFacade;
	
	/** @var EntityManager */
	private $em;
	
	/** @var EntityDao */
	private $roleDao;
	
	/** @var UserFacade */
	private $userFacade;
	
	/** @var array */
	private $roles;


	public function __construct(IFormFactory $formFactory, \App\Model\Facade\Roles $roleFacade, EntityManager $em, UserFacade $userFacade)
	{
		parent::__construct($formFactory);
		$this->em = $em;
		$this->roleDao = $this->em->getDao(Role::getClassName());
		$this->roleFacade = $roleFacade;
		$this->userFacade = $userFacade;
	}

	private function getRoles()
	{
		if ($this->roles === NULL) {
			$this->roles = $this->roleDao->findPairs('name', 'id');
		}
		return $this->roles;
	}

	public function create()
	{
		$form = $this->formFactory->create();
		$form->addText('username', 'Username')
				->setOption('description', 'username must be e-mail')
				->addRule(Form::EMAIL, 'Username must be e-mail')
				->addRule(Form::FILLED, 'Username must be filled')
				->addRule(function(\Nette\Forms\Controls\TextInput $item) {
//					return $this->entity ;//$this->userFacade->isUnique($item->value);
				}, 'This e-mail is used yet!');
				
		$password = $form->addText('password', 'Password');
		if ($this->isAdding()) {
			$password->addRule(Form::FILLED, 'Password must be filled');
		}
		
		$role = $form->addMultiSelect2('roles', 'Roles', $this->getRoles())
				->setRequired('Select any role');

		$defaultRole = $this->roleFacade->findByName('client');
		if ($defaultRole) {
			$role->setDefaultValue($defaultRole->getId());
		}

		$form->addSubmit('_submit', 'Save');
		$form->addSubmit('submitContinue', 'Save and continue edit');
		return $form;
	}

}
