<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Company;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Exception;
use Kdyby\Doctrine\EntityManager;
use Nette\Forms\IControl;
use Nette\Utils\ArrayHash;

class CompleteAccountControl extends BaseControl
{

	// <editor-fold desc="events">

	/** @var array */
	public $onCreateCandidate = [];

	/** @var array */
	public $onCreateCompany = [];

	/** @var array */
	public $onMissingUser = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	// </editor-fold>

	/** @var int */
	private $userId;

	/** @var User */
	private $user;

	/**
	 * Set user id to complete account
	 * @param int $userId
	 * @return self
	 */
	public function setUserId($userId)
	{
		$this->userId = $userId;
		return $this;
	}

	/**
	 * Load and return user
	 * @return User
	 */
	private function getUser()
	{
		if (!$this->user) {
			$this->user = $this->em->getDao(User::getClassName())->find($this->userId);
		}
		return $this->user;
	}

	public function render()
	{
		$requiredRole = (string) $this->getUser()->requiredRole;
		switch ($requiredRole) {
			case Role::CANDIDATE:
				$this->setTemplateFile('candidate');
				break;
			case Role::COMPANY:
				$this->setTemplateFile('company');
				break;
			default:
				$this->setTemplateFile('none');
				break;
		}
		parent::render();
	}

	/** @return Form */
	protected function createComponentCandidateForm()
	{
		$form = new Form;
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		if (!$this->getUser()->id) {
			throw new CompleteAccountControlException('Use setUserId($id) with existed ID');
		}

		$form->addText('fullName', 'Name')
				->setAttribute('placeholder', 'name and surename')
				->setRequired('Please enter your name.')
				->setDefaultValue($this->getUser()->socialName);

		$form->addDateInput('birthday', 'Birthday')
				->setDefaultValue($this->getUser()->socialBirthday);

		$form->addSubmit('confirm', 'Confirm');

		$form->onSuccess[] = $this->candidateFormSucceeded;
		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function candidateFormSucceeded(Form $form, ArrayHash $values)
	{
		$roleDao = $this->em->getDao(Role::getClassName());
		$userRepo = $this->em->getRepository(User::getClassName());

		if (!$this->getUser()->id) {
			throw new CompleteAccountControlException('Use setUserId($id) with existed ID');
		}
		$user = $userRepo->find($this->getUser()->id);
		if ($user->candidate !== NULL) {
			throw new CompleteAccountControlException('This user is already candidate');
		}

		$requiredRole = $roleDao->find($this->getUser()->requiredRole->id);
		$user->addRole($requiredRole);
		$user->removeRole($this->roleFacade->findByName(Role::SIGNED));
		$user->candidate->name = $values->fullName;
		$user->candidate->birthday = $values->birthday;
		$savedUser = $userRepo->save($user);

		$this->onCreateCandidate($this, $savedUser->candidate);
	}

	/** @return Form */
	protected function createComponentCompanyForm()
	{
		$form = new Form;
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addServerValidatedText('name', 'Company')
				->setAttribute('placeholder', 'Company name')
				->setRequired('Please enter your company\'s name.')
				->addServerRule([$this, 'validateCompanyName'], $this->translator->translate('%s is already registered.'));

		$form->addServerValidatedText('companyId', 'Company ID')
				->setAttribute('placeholder', 'Company identification')
				->setRequired('Please enter company identification.')
				->addServerRule([$this, 'validateCompanyId'], $this->translator->translate('%s is already registered.'));

		// TODO: do it by addAddress() (do this control)
		$form->addTextArea('address', 'Address')
				->setAttribute('placeholder', 'Company full address');

		$form->addSubmit('confirm', 'Confirm');

		$form->onSuccess[] = $this->companyFormSucceeded;
		return $form;
	}

	public function validateCompanyName(IControl $control, $arg = NULL)
	{
		return $this->companyFacade->isUniqueName($control->getValue());
	}

	public function validateCompanyId(IControl $control, $arg = NULL)
	{
		return $this->companyFacade->isUniqueId($control->getValue());
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function companyFormSucceeded(Form $form, ArrayHash $values)
	{
		$userDao = $this->em->getDao(User::getClassName());

		// create company with admin access
		$company = new Company;
		$company->name = $values->name;
		$company->companyId = $values->companyId;
		$company->address = $values->address;
		$createdCompany = $this->companyFacade->create($company, $this->getUser());

		// add role to user
		$requiredRole = $this->roleFacade->findByName(Role::COMPANY);
		$user = $this->getUser();
		$user->addRole($requiredRole);
		$user->removeRole($this->roleFacade->findByName(Role::SIGNED));
		$userDao->save($user);

		$this->onCreateCompany($this, $createdCompany);
	}

}

class CompleteAccountControlException extends Exception
{

}

interface ICompleteAccountControlFactory
{

	/** @return CompleteAccountControl */
	function create();
}
