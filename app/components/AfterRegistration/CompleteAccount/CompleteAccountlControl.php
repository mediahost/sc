<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Candidate;
use App\Model\Entity\Company;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\UserFacade;
use Exception;
use Kdyby\Doctrine\EntityManager;
use Nette\Utils\ArrayHash;

class CompleteAccountControl extends BaseControl
{
	// <editor-fold defaultstate="expoanded" desc="events">

	/** @var array */
	public $onCreateCandidate = [];

	/** @var array */
	public $onCreateCompany = [];

	/** @var array */
	public $onMissingUser = [];

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var UserFacade @inject */
	public $userFacade;

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
		$candidateDao = $this->em->getDao(Candidate::getClassName());
		$roleDao = $this->em->getDao(Role::getClassName());
		$userDao = $this->em->getDao(User::getClassName());
		
		if (!$this->getUser()->id) {
			throw new CompleteAccountControlException('Use setUserId($id) with existed ID');
		}
		if ($candidateDao->findOneBy(['user' => $this->getUser()])) {
			throw new CompleteAccountControlException('This user is already candidate');
		}
		
		$candidate = new Candidate;
		$candidate->user = $this->getUser();
		$candidate->name = $values->fullName;
		$candidate->birthday = $values->birthday;
		$candidateDao->save($candidate);
		
		$requiredRole = $roleDao->find($this->getUser()->requiredRole->id);
		$candidate->user->addRole($requiredRole);
		$userDao->save($candidate->user);

		$this->onCreateCandidate($this, $candidate);
	}

	/** @return Form */
	protected function createComponentCompanyForm()
	{
		$form = new Form;
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addText('name', 'Company')
				->setAttribute('placeholder', 'Company name')
				->setRequired('Please enter your company\'s name.');

		$form->addText('company_id', 'ID')
				->setAttribute('placeholder', 'Company ID');

		// TODO: do it by addAddress() (do this control)
		$form->addTextArea('address', 'Address')
				->setAttribute('placeholder', 'Company full address');

		$form->addSubmit('confirm', 'Confirm');

		$form->onSuccess[] = $this->companyFormSucceeded;
		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function companyFormSucceeded(Form $form, ArrayHash $values)
	{
		$company = new Company;
		$company->name = $values->name;
		// TODO: create company

		$this->onCreateCompany($this, $company);
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
