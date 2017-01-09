<?php

namespace App\Components\Company;

use App\Components\BaseControl;
use App\Components\User\ICompanyUserFactory;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity;
use App\Model\Entity\Company;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\Role;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Nette\Forms\IControl;
use Nette\Security\User;
use Nette\Utils\ArrayHash;
use Nette\Utils\Html;

class CompanyInfo extends BaseControl
{

	/** @var Company */
	private $company;

	/** @var User */
	private $user;

	/** @var Entity\User */
	private $selectedUser;

	/** @var array */
	private $usersRoles = [];

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var ICompanyUserFactory @inject */
	public $iCompanyUserFactory;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var bool */
	private $canEditInfo = FALSE;

	/** @var bool */
	private $canEditUsers = FALSE;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		if ($this->canEditInfo) {
			$form->addText('companyId', 'Company ID')
				->setAttribute('placeholder', 'Company identification')
				->setRequired('Please enter company\'s identification.');
			$form->addText('name', 'Name')
				->setAttribute('placeholder', 'Company name')
				->setRequired('Please enter your company\'s name.');
			$form->addTextArea('address', 'Address')
				->setAttribute('placeholder', 'Company Address');
		}

		if ($this->canEditUsers) {
			$form->addCheckbox('add', 'Create Company user')
				->setDefaultValue(TRUE)
				->addCondition($form::EQUAL, TRUE)
				->toggle('user-name')
				->toggle('user-password')
				->toggle('admins', FALSE);

			$users = $this->userFacade->getUserMailsInRole($this->roleFacade->findByName(Role::COMPANY));
			$admins = $form->addMultiSelect2('admins', 'Administrators', $users)
				->setOption('id', 'admins');
			$admins->addConditionOn($form['add'], Form::EQUAL, FALSE)
				->setRequired('Company must have administrator');

			$mail = $form->addText('userMail', 'User Mail')
				->setOption('id', 'user-name');
			$mail->addConditionOn($form['add'], Form::EQUAL, TRUE)
				->addRule(Form::FILLED, 'Must be filled')
				->addRule(Form::EMAIL, 'Must be email');
			$password = $form->addText('userPassword', 'User password')
				->setOption('id', 'user-password');
			$password->addConditionOn($form['add'], Form::EQUAL, TRUE)
				->addRule(Form::FILLED, 'Must be filled');
		}

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$id = $this->company ? $this->company->id : NULL;
		if (!$this->companyFacade->isUniqueId($values->companyId, $id)) {
			$message = $this->translator->translate('\'%name%\' is already registered.', ['name' => $values->companyId]);
			$form['companyId']->addError($message);
		}
		if (!$this->companyFacade->isUniqueName($values->name, $id)) {
			$message = $this->translator->translate('\'%name%\' is already registered.', ['name' => $values->name]);
			$form['name']->addError($message);
		}

		if (!$form->hasErrors()) {
			$this->load($values);
			$this->save();
			$this->onAfterSave($this->company);
		}
	}

	private function load(ArrayHash $values)
	{
		if ($this->canEditInfo) {
			$this->company->name = $values->name;
			$this->company->companyId = $values->companyId;
			$this->company->address = $values->address;
		}

		if ($this->canEditUsers) {
			if ($values->add) {
				$roleRepo = $this->em->getRepository(Role::getClassName());
				$userRepo = $this->em->getRepository(Entity\User::getClassName());
				$role = $roleRepo->findOneByName(Role::COMPANY);

				$admin = new Entity\User($values->userMail, TRUE);
				$admin->setPassword($values->userPassword);
				$admin->addRole($role);
				$userRepo->save($admin);

				$this->usersRoles[$admin->id][] = CompanyRole::ADMIN;
			} else {
				foreach ($values->admins as $adminId) {
					$this->usersRoles[$adminId][] = CompanyRole::ADMIN;
				}
			}
		}
		return $this;
	}

	private function save()
	{
		$this->em->persist($this->company);
		$this->em->flush();
		if ($this->canEditUsers) {
			$this->companyFacade->clearPermissions($this->company);
			foreach ($this->usersRoles as $userId => $userRoles) {
				$this->companyFacade->addPermission($this->company, $userId, $userRoles);
			}
		}
		return $this;
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [
			'name' => $this->company->name,
			'companyId' => $this->company->companyId,
			'address' => $this->company->address,
		];
		if ($this->canEditUsers) {
			foreach ($this->company->adminAccesses as $adminPermission) {
				$values['admins'][] = $adminPermission->user->id;
			}
			if ($this->selectedUser) {
				$values['admins'][] = $this->selectedUser->id;
			}
		}
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->company) {
			throw new CompanyException('Use setCompany(\App\Model\Entity\Company) before render');
		}
	}

	// <editor-fold desc="setters & getters">

	public function setCompany(Company $company)
	{
		$this->company = $company;
		return $this;
	}

	public function setUser(User $user)
	{
		$this->user = $user;
		$this->canEditInfo = $this->user->isAllowed('company', 'edit');
		$this->canEditUsers = $this->user->isAllowed('company', 'edit');
	}

	public function selectUser(Entity\User $user)
	{
		$this->selectedUser = $user;
	}

	// </editor-fold>

	/** @return CompanyUser */
	public function createComponentEditUserForm()
	{
		$control = $this->iCompanyUserFactory->create();
		$control->onAfterSave = function (Entity\User $saved) {
			$this->selectUser($saved);
			$message = $this->translator->translate('User \'%user%\' was successfully saved.', ['user' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redrawControl('companyInfo');
		};
		return $control;
	}
}

interface ICompanyInfoFactory
{

	/** @return CompanyInfo */
	function create();
}
