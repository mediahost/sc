<?php

namespace App\Components\Company;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Company;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\Role;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Exception;
use Nette\Forms\IControl;
use Nette\Utils\ArrayHash;
use Nette\Utils\Html;

/**
 * Form with all company settings.
 */
class CompanyControl extends BaseControl
{

	/** @var Company */
	private $company;

	/** @var array */
	private $usersRoles = [];

	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var bool */
	private $canEditInfo = FALSE;

	/** @var bool */
	private $canEditUsers = FALSE;

	/** @var Html */
	private $linkAddUser;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer);

		if ($this->canEditInfo) {
			$form->addServerValidatedText('companyId', 'Company ID')
					->setAttribute('placeholder', 'Company identification')
					->setRequired('Please enter company\'s identification.')
					->addServerRule([$this, 'validateCompanyId'], $this->translator->translate('%s is already registered.'));
			$form->addServerValidatedText('name', 'Name')
					->setAttribute('placeholder', 'Company name')
					->setRequired('Please enter your company\'s name.')
					->addServerRule([$this, 'validateCompanyName'], $this->translator->translate('%s is already registered.'));
			$form->addTextArea('address', 'Address')
					->setAttribute('placeholder', 'Company Address');
		}

		if ($this->canEditUsers) {
			$users = $this->userFacade->getUserMailsInRole($this->roleFacade->findByName(Role::COMPANY));
			$admins = $form->addMultiSelect2('admins', 'Administrators', $users)
					->setRequired('Company must have administrator');
			$managers = $form->addMultiSelect2('managers', 'Managers', $users);
			$editors = $form->addMultiSelect2('editors', 'Editors', $users);

			if ($this->linkAddUser) {
				$this->linkAddUser->setText($this->translator->translate('add new user'));
				$message = Html::el('')
						->setText($this->translator->translate('You can') . ' ')
						->add($this->linkAddUser);
				$admins->setOption('description', $message);
				$managers->setOption('description', $message);
				$editors->setOption('description', $message);
			}
		}

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function validateCompanyId(IControl $control, $arg = NULL)
	{
		$id = $this->company ? $this->company->id : NULL;
		return $this->companyFacade->isUniqueId($control->getValue(), $id);
	}

	public function validateCompanyName(IControl $control, $arg = NULL)
	{
		$id = $this->company ? $this->company->id : NULL;
		return $this->companyFacade->isUniqueName($control->getValue(), $id);
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->company);
	}

	private function load(ArrayHash $values)
	{
		if ($this->canEditInfo) {
			$this->company->name = $values->name;
			$this->company->companyId = $values->companyId;
			$this->company->address = $values->address;
		}
		if ($this->canEditUsers) {
			foreach ($values->admins as $adminId) {
				$this->usersRoles[$adminId][] = CompanyRole::ADMIN;
			}
			foreach ($values->managers as $managerId) {
				$this->usersRoles[$managerId][] = CompanyRole::MANAGER;
			}
			foreach ($values->editors as $editorId) {
				$this->usersRoles[$editorId][] = CompanyRole::EDITOR;
			}
		}
		return $this;
	}

	private function save()
	{
		$companyDao = $this->em->getDao(Company::getClassName());
		$savedCompany = $companyDao->save($this->company);
		if ($this->canEditUsers) {
			$this->companyFacade->clearPermissions($savedCompany);
			foreach ($this->usersRoles as $userId => $userRoles) {
				$this->companyFacade->addPermission($savedCompany, $userId, $userRoles);
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
			foreach ($this->company->managerAccesses as $managerPermission) {
				$values['managers'][] = $managerPermission->user->id;
			}
			foreach ($this->company->editorAccesses as $editorPermission) {
				$values['editors'][] = $editorPermission->user->id;
			}
		}
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->company) {
			throw new CompanyControlException('Use setCompany(\App\Model\Entity\Company) before render');
		}
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	public function setCompany(Company $company)
	{
		$this->company = $company;
		return $this;
	}

	public function setCanEditInfo($value = TRUE)
	{
		$this->canEditInfo = $value;
		return $this;
	}

	public function setCanEditUsers($value = TRUE)
	{
		$this->canEditUsers = $value;
		return $this;
	}

	public function setLinkAddUser($link, array $attributes = [])
	{
		$this->linkAddUser = Html::el('a')
				->href($link)
				->addAttributes($attributes);
		return $this;
	}

	// </editor-fold>
}

class CompanyControlException extends Exception
{
	
}

interface ICompanyControlFactory
{

	/** @return CompanyControl */
	function create();
}
