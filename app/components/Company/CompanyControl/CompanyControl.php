<?php

namespace App\Components\Company;

use App\Components\EntityControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Company;
use App\Model\Entity\CompanyRole;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\UserFacade;
use Kdyby\Doctrine\EntityManager;
use Nette\Forms\IControl;
use Nette\Utils\ArrayHash;
use Nette\Utils\Html;

/**
 * Form with all company settings.
 * 
 * @method self setEntity(Company $entity)
 * @method Company getEntity()
 * @property Company $entity
 */
class CompanyControl extends EntityControl
{
	// <editor-fold defaultstate="expanded" desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var UserFacade @inject */
	public $userFacade;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var bool */
	private $canEditInfo = FALSE;

	/** @var bool */
	private $canEditUsers = FALSE;

	/** @var string */
	private $linkAddUser;

	/** @var array */
	private $linkAddUserAttrs = [];

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer);

		if ($this->canEditInfo) {
			$form->addServerValidatedText('companyId', 'Company ID')
					->setRequired('Please enter company ID.')
					->addServerRule([$this, 'validateCompanyId'], $this->translator->translate('%s is already registered.'));
			$form->addText('name', 'Name')
					->setAttribute('placeholder', 'Company Name')
					->setRequired('Please enter company name.');
			$form->addTextArea('address', 'Address')
					->setAttribute('placeholder', 'Company Address');
		}

		if ($this->canEditUsers) {
			$users = $this->userFacade->getUsers();
			$admins = $form->addMultiSelect2('admins', 'Administrators', $users)
					->setRequired('Company must have administrator');
			$managers = $form->addMultiSelect2('managers', 'Managers', $users);
			$editors = $form->addMultiSelect2('editors', 'Editors', $users);

			if ($this->linkAddUser) {
				$link = Html::el('a')
						->setText($this->translator->translate('add new user'));
				$link->href($this->linkAddUser);
				$link->addAttributes($this->linkAddUserAttrs);
				$message = Html::el('')
						->setText($this->translator->translate('You can') . ' ')
						->add($link);
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
		$id = $this->entity ? $this->entity->id : NULL;
		return $this->companyFacade->isUnique($control->getValue(), $id);
	}

	public function formSucceeded(Form $form, $values)
	{
		$entity = $this->load($values);
		$companyDao = $this->em->getDao(Company::getClassName());
		$saved = $companyDao->save($entity);
		$this->onAfterSave($saved);
	}

	/**
	 * Load Entity from Form
	 * @param ArrayHash $values
	 * @return Company
	 */
	protected function load(ArrayHash $values)
	{
		$entity = $this->getEntity();
		if ($this->canEditInfo) {
			$entity->name = $values->name;
			$entity->companyId = $values->companyId;
			$entity->address = $values->address;
		}
		if ($this->canEditUsers) {
			$usersRoles = [];
			foreach ($values->admins as $adminId) {
				$usersRoles[$adminId][] = CompanyRole::ADMIN;
			}
			foreach ($values->managers as $managerId) {
				$usersRoles[$managerId][] = CompanyRole::MANAGER;
			}
			foreach ($values->editors as $editorId) {
				$usersRoles[$editorId][] = CompanyRole::EDITOR;
			}
			foreach ($usersRoles as $userId => $userRoles) {
				$this->companyFacade->addPermission($this->entity, $userId, $userRoles);
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
		$company = $this->getEntity();
		$values = [
			'name' => $company->name,
			'companyId' => $company->companyId,
			'address' => $company->address,
		];
		if ($this->canEditUsers) {
			foreach ($company->adminAccesses as $adminPermission) {
				$values['admins'][] = $adminPermission->user->id;
			}
			foreach ($company->managerAccesses as $managerPermission) {
				$values['managers'][] = $managerPermission->user->id;
			}
			foreach ($company->editorAccesses as $editorPermission) {
				$values['editors'][] = $editorPermission->user->id;
			}
		}
		return $values;
	}

	// <editor-fold defaultstate="collapsed" desc="setters & getters">

	protected function checkEntityType($entity)
	{
		return $entity instanceof Company;
	}

	/** @return Company */
	protected function getNewEntity()
	{
		return new Company;
	}

	/** @return self */
	public function setCanEditInfo($value = TRUE)
	{
		$this->canEditInfo = $value;
		return $this;
	}

	/** @return self */
	public function setCanEditUsers($value = TRUE)
	{
		$this->canEditUsers = $value;
		return $this;
	}

	/** @return self */
	public function setLinkAddUser($link, array $attributes = [])
	{
		$this->linkAddUser = $link;
		$this->linkAddUserAttrs = $attributes;
		return $this;
	}

	// </editor-fold>
}

interface ICompanyControlFactory
{

	/** @return CompanyControl */
	function create();
}
