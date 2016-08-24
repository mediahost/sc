<?php

namespace App\Components\User;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Company;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Nette\Forms\IControl;

/**
 * Only for create company user
 */
class CompanyUserControl extends BaseControl
{
	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold desc="injects">

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

	/** @var User */
	private $user;

	/** @var array */
	private $companies;

	/** @var array */
	private $roles;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer);
        $form->getElementPrototype()->class('ajax');
                    
		$user = $form->addServerValidatedText('mail', 'E-mail')
				->addRule(Form::EMAIL, 'Fill right format')
				->addRule(Form::FILLED, 'Mail must be filled')
				->addServerRule([$this, 'validateMail'], $this->translator->translate('%s is already registered.'));
		if ($this->user) {
			$user->setDisabled();
		} else {
			$helpText = $this->translator->translate('At least %count% characters long.', $this->settings->passwords->length);
			$helpText->setTranslator($this->translator);
			$form->addText('password', 'Password')
					->addRule(Form::FILLED, 'Password must be filled')
					->addRule(Form::MIN_LENGTH, 'Password must be at least %d characters long.', $this->settings->passwords->length)
					->setOption('description', (string) $helpText);
		}


		$form->addServerMultiSelectBoxes('roles', 'Roles', $this->getRoles())
				->setRequired('Please select some role')
				->addServerRule([$this, 'validateAdminRoles'], $this->translator->translate('Company must have administrator.'));

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function validateMail(IControl $control, $arg = NULL)
	{
		return $this->userFacade->isUnique($control->getValue());
	}

	public function validateAdminRoles(IControl $control, $arg = NULL)
	{
		// new user
		if (!$this->user) {
			return TRUE;
		}
		// values contain admin
		$adminRole = $this->companyFacade->findRoleByName(CompanyRole::ADMIN);
		if (in_array($adminRole->id, $control->getValue())) {
			return TRUE;
		}
		// check if company has another admin
		$company = $this->company ? $this->company : $this->companyFacade->find($control->getParent()->values->company);
		foreach ($this->companyFacade->findPermissions($company) as $permission) {
			if ($permission->user->id !== $this->user->id && $permission->containRoleName(CompanyRole::ADMIN)) {
				return TRUE;
			}
		}
		return FALSE;
	}

	public function formSucceeded(Form $form, $values)
	{
		// user
		if ($this->user) {
			$user = $this->em->getDao(User::getClassName())->find($this->user->id);
		} else {
			$role = $this->roleFacade->findByName(Role::COMPANY);
			$user = $this->userFacade->create($values->mail, $values->password, $role);
		}
		$this->onAfterSave($user);
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [];
		if ($this->company) {
			$values['company'] = $this->company->id;
		}
		if ($this->user) {
			$values['mail'] = $this->user->mail;
		}
		if ($this->company && $this->user) {
			$permission = $this->companyFacade->findPermission($this->company, $this->user);
			$values['roles'] = $permission->rolesKeys;
		}
		return $values;
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
		return $this;
	}

	/** @return array */
	private function getRoles()
	{
		if ($this->roles === NULL) {
			$this->roles = $this->companyFacade->getRolesNames();
		}
		return $this->roles;
	}

	/** @return array */
	private function getCompanies()
	{
		if ($this->companies === NULL) {
			$this->companies = $this->companyFacade->getCompaniesNames();
		}
		return $this->companies;
	}

	// </editor-fold>
}

interface ICompanyUserControlFactory
{

	/** @return CompanyUserControl */
	function create();
}
