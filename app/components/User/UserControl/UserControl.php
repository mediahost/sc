<?php

namespace App\Components\User;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\TaggedString;
use Kdyby\Doctrine\DuplicateEntryException;
use Kdyby\Doctrine\EntityManager;
use Nette\Utils\ArrayHash;

/**
 * Form with all user's personal settings.
 */
class UserControl extends BaseControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var User */
	private $user;

	/** @var array */
	private $identityRoles = [];

	/** @var array */
	private $roles;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var EntityManager @inject */
	public $em;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$mail = $form->addText('mail', 'Mail')
				->addRule(Form::EMAIL, 'Fill right format')
				->addRule(Form::FILLED, 'Mail must be filled');
		if ($this->isUserExists()) {
			$mail->setDisabled();
		}

		$password = $form->addText('password', 'Password');
		if (!$this->isUserExists()) {
			$password->addRule(Form::FILLED, 'Password must be filled');
		}

		$role = $form->addMultiSelect2('roles', 'Roles', $this->getRoles())
				->setRequired('Select any role');

		$defaultRole = $this->roleFacade->findByName(Role::ROLE_CANDIDATE);
		if ($defaultRole && in_array($defaultRole->getId(), $this->getRoles())) {
			$role->setDefaultValue($defaultRole->getId());
		}

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$entity = $this->load($values);
		$userDao = $this->em->getDao(User::getClassName());
		try {
			$saved = $userDao->save($entity);
			$this->onAfterSave($saved);
		} catch (DuplicateEntryException $exc) {
			$message = new TaggedString('\'<%mail%>\' is already registred', ['mail' => $values->mail]);
			$form['mail']->addError($message);
		}
	}

	/**
	 * Load Entity from Form
	 * @param ArrayHash $values
	 * @return User
	 */
	private function load(ArrayHash $values)
	{
		$entity = $this->getUser();
		if (isset($values->mail)) {
			$entity->mail = $values->mail;
		}
		if ($values->password !== NULL && $values->password !== "") {
			$entity->setPassword($values->password);
		}
		$entity->clearRoles();
		foreach ($values->roles as $id) {
			$roleDao = $this->em->getDao(Role::getClassName());
			$item = $roleDao->find($id);
			if ($item) {
				$entity->addRole($item);
			}
		}
		return $entity;
	}

	/**
	 * Get Entity for Form
	 * @return array
	 */
	private function getDefaults()
	{
		$user = $this->getUser();
		$values = [
			'mail' => $user->mail,
			'roles' => $user->getRolesKeys(),
		];
		return $values;
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	/**
	 * @param User $user
	 * @return self
	 */
	public function setUser(User $user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * @return User
	 */
	public function getUser()
	{
		if ($this->user) {
			return $this->user;
		} else {
			return new User;
		}
	}

	private function isUserExists()
	{
		return $this->getUser()->id !== NULL;
	}
	
	public function setIdentityRoles(array $roles)
	{
		$this->identityRoles = $roles;
	}

	private function getRoles()
	{
		if ($this->roles === NULL) {
			$this->roles = $this->roleFacade->findLowerRoles($this->identityRoles);
		}
		return $this->roles;
	}

	// </editor-fold>
}

interface IUserControlFactory
{

	/** @return UserControl */
	function create();
}
