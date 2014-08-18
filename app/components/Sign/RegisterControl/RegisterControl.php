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
class RegisterControl extends Control
{
		
	/** @var \Kdyby\Doctrine\EntityManager */
	private $em;
	
	/** @var \App\Model\Storage\RegistrationStorage */
	private $registration;
	
	/** @var \App\Model\Facade\Users */
	private $users;
	
	
	public function __construct(\Kdyby\Doctrine\EntityManager $em, \App\Model\Storage\RegistrationStorage $reg, \App\Model\Facade\Users $users)
	{
		$this->em = $em;
		$this->registration = $reg;
		$this->users = $users;
	}

    public function render()
    {
        $template = $this->template;
		$template->oauth = $this->registration->isOauth();
		$template->birthdate = $this->registration->isRequired('birthdate');
		$template->email = $this->registration->isRequired('email');
        $template->setFile(__DIR__ . '/render.latte');
        $template->render();
    }

	/**
	 * Sign in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentRegisterForm()
	{
		\Tracy\Debugger::barDump($this->registration->data);
		
		$form = new Form();
		$form->getElementPrototype()->addAttributes(['autocomplete' => 'off']);
        $form->setRenderer(new \App\Forms\Renderers\MetronicFormRenderer());
        
        $form->addText('name', 'Name')
                ->setRequired('Please enter your username')
                ->setAttribute('placeholder', 'Full name');
		
		if ($this->registration->isRequired('birthdate')) {
			$form->addText('birthdate', 'Birthdate')
				    ->setRequired('Please enter your username')
					->setAttribute('placeholder', 'Birthdate');
		}
		
		if ($this->registration->isRequired('email')) {
			$form->addText('email', 'E-mail')
				    ->setRequired('Please enter your e-mail')
					->setAttribute('placeholder', 'E-mail')
					->setAttribute('autocomplete', 'off')
					->addRule(function(Nette\Forms\Controls\TextInput $item){
						        return $this->users->isUnique($item->value);
						}, 'This e-mail is used yet!');
		}

		if ($this->registration->isOauth()) {
			$form->setDefaults($this->registration->defaults);
		} else {
			$form->addPassword('password', 'Password')
					->setRequired('Please enter your password')
					->setAttribute('placeholder', 'Password');
		}


		
        $form->addSubmit('register', 'Register');


		// call method signInFormSucceeded() on success
		$form->onSuccess[] = $this->registerFormSucceeded;
		return $form;
	}

	public function registerFormSucceeded(Form $form, $values)
	{
		if ($this->registration->isOauth()) {
			$user = $this->registrationStorage->user;
			// ToDo: Uložit access token
		} else {
			$user = new Entity\User();
			
			$auth = new Entity\Auth();
			$auth->hash = \Nette\Security\Passwords::hash($values->hast);
			$auth->key = $values->email;
			$auth->source = 'app';
			$user->addAuth($auth);
		}
		
		// ToDo: Uložit data z formuláře
		$user->email = $values->email;		
		
		$role = $this->em->getDao(Entity\Role::getClassName())->findOneBy(['name' => 'superadmin']);
		$user->addRole($role);
		
		$this->em->persist($user);
		$this->em->flush();
		
		$this->presenter->redirect(':Admin:Dashboard:');
	}
}

interface IRegisterControlFactory
{
    /** @return RegisterControl */
    function create();
}
