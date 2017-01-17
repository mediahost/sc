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
use Nette\Security\User;
use Nette\Utils\ArrayHash;

class CompanyInfo extends BaseControl
{

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var User @inject */
	public $user;

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

	/** @var Company */
	private $company;

	/** @var array */
	private $usersRoles = [];

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		$form->addText('companyId', 'Company ID')
			->setAttribute('placeholder', 'Company identification')
			->setRequired('Please enter company\'s identification.');
		$form->addText('name', 'Name')
			->setAttribute('placeholder', 'Company name')
			->setRequired('Please enter your company\'s name.');

//			$admins = $form->addMultiSelect2('admins', 'Administrators', $companyUsers)
//				->setOption('id', 'admins');
		$companyUsers = $this->userFacade->getUserMailsInRole($this->roleFacade->findByName(Role::COMPANY));
		$form->addMultiSelect2('jobbers', 'Job managers', $companyUsers);

		$form->addCheckbox('add', 'Create Company user')
			->setDefaultValue($this->company->isNew())
			->addCondition($form::EQUAL, TRUE)
			->toggle('user-name')
			->toggle('user-password');
//				->toggle('user-role')
//				->toggle('admins', FALSE);

//			$admins->addConditionOn($form['add'], Form::EQUAL, FALSE)
//				->setRequired('Company must have administrator');

		$mail = $form->addText('userMail', 'User Mail')
			->setOption('id', 'user-name');
		$mail->addConditionOn($form['add'], Form::EQUAL, TRUE)
			->addRule(Form::FILLED, 'Must be filled')
			->addRule(Form::EMAIL, 'Must be email');
		$password = $form->addText('userPassword', 'User password')
			->setOption('id', 'user-password');
		$password->addConditionOn($form['add'], Form::EQUAL, TRUE)
			->addRule(Form::FILLED, 'Must be filled');
//			$roles = $this->companyFacade->getRolesNames();
//			$form->addSelect2('companyRole', 'Role', $roles)
//				->setOption('id', 'user-role');

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
		if ($values->add && isset($values->userMail) && !$this->userFacade->isUnique($values->userMail)) {
			$message = $this->translator->translate('E-mail \'%mail%\' is already registered.', ['mail' => $values->userMail]);
			$form['userMail']->addError($message);
		}

		if (!$form->hasErrors()) {
			$this->load($values);
			$this->save();
			$this->onAfterSave($this->company);
		}
	}

	private function load(ArrayHash $values)
	{
		if ($this->user->isAllowed('company', 'edit')) {
			$this->company->name = $values->name;
			$this->company->companyId = $values->companyId;

			if (isset($values->admins)) {
				foreach ($values->admins as $userId) {
					$this->usersRoles[$userId][] = CompanyRole::ADMIN;
				}
			}
			if (isset($values->jobbers)) {
				foreach ($values->jobbers as $userId) {
					$this->usersRoles[$userId][] = CompanyRole::JOBBER;
				}
			}

			if ($values->add) {
				$roleRepo = $this->em->getRepository(Role::getClassName());
				$userRepo = $this->em->getRepository(Entity\User::getClassName());
				$role = $roleRepo->findOneByName(Role::COMPANY);

				$companyUser = new Entity\User($values->userMail, TRUE);
				$companyUser->setPassword($values->userPassword);
				$companyUser->addRole($role);
				$userRepo->save($companyUser);

				$this->usersRoles[$companyUser->id][] = CompanyRole::JOBBER;
			}
		}
		return $this;
	}

	private function save()
	{
		$this->em->persist($this->company);
		$this->em->flush();

		if ($this->user->isAllowed('company', 'edit')) {
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
		$companyRoleRepo = $this->em->getRepository(CompanyRole::getClassName());
		$companyRole = $companyRoleRepo->findOneByName(CompanyRole::ADMIN);
		$values = [
			'name' => $this->company->name,
			'companyId' => $this->company->companyId,
			'street' => $this->company->address->street,
			'companyRole' => $companyRole->id,
		];
		foreach ($this->company->adminAccesses as $permission) {
			$values['admins'][] = $permission->user->id;
		}
		foreach ($this->company->jobberAccesses as $permission) {
			$values['jobbers'][] = $permission->user->id;
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

	// </editor-fold>
}

interface ICompanyInfoFactory
{

	/** @return CompanyInfo */
	function create();
}
