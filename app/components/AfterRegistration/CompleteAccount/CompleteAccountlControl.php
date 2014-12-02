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
use Nette\Utils\ArrayHash;

class CompleteAccountControl extends BaseControl
{

	/** @var array */
	public $onCreateCandidate = [];

	/** @var array */
	public $onCreateCompany = [];

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var User */
	private $user;

	/**
	 * Set user to complete account
	 * If null then set role to candidate
	 * @param User $user
	 * @return self
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	public function render()
	{
		$requiredRole = (string) $this->user->requiredRole;
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

		$form->addText('fullName', 'Name')
				->setAttribute('placeholder', 'name and surename')
				->setRequired('Please enter your name.')
				->setDefaultValue($this->user->socialName);

		$form->addDatePicker('birthday', 'Birthday')
				->setAttribute('placeholder', 'dd/mm/yyyy');

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
		$candidate = new Candidate;
		$candidate->name = $values->fullName;
		// TODO: create candidate

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

interface ICompleteAccountControlFactory
{

	/** @return CompleteAccountControl */
	function create();
}
