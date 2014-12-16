<?php

namespace App\Components\User;

use App\Components\EntityControl;
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
 * 
 * @method self setEntity(User $entity)
 * @method User getEntity()
 * @property User $entity
 */
class UserControl extends EntityControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

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
		$form->setRenderer(new MetronicFormRenderer);

		$mail = $form->addText('mail', 'Mail')
				->addRule(Form::EMAIL, 'Fill right format')
				->addRule(Form::FILLED, 'Mail must be filled');
		if ($this->isEntityExists()) {
			$mail->setDisabled();
		}

		$password = $form->addText('password', 'Password');
		if (!$this->isEntityExists()) {
			$helpText = new TaggedString('At least %d characters long.', $this->passwordService->length);
			$helpText->setTranslator($this->translator);
			$password->addRule(Form::FILLED, 'Password must be filled')
					->addRule(Form::MIN_LENGTH, 'Password must be at least %d characters long.', $this->passwordService->length)
					->setOption('description', (string) $helpText);
		}

		$role = $form->addMultiSelectBoxes('roles', 'Roles', $this->getRoles())
				->setRequired('Select any role');

		$defaultRole = $this->roleFacade->findByName(Role::CANDIDATE);
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
			$message = new TaggedString('\'%s\' is already registred', $values->mail);
			$form['mail']->addError($message);
		}
	}

	/**
	 * Load Entity from Form
	 * @param ArrayHash $values
	 * @return User
	 */
	protected function load(ArrayHash $values)
	{
		$entity = $this->getEntity();
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
	protected function getDefaults()
	{
		$user = $this->getEntity();
		$values = [
			'mail' => $user->mail,
			'roles' => $user->getRolesKeys(),
		];
		return $values;
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	protected function checkEntityType($entity)
	{
		return $entity instanceof User;
	}

	/** @return User */
	protected function getNewEntity()
	{
		return new User;
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
