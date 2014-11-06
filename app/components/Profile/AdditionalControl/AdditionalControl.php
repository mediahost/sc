<?php

namespace App\Components\Profile;

use App\Components\BaseControl;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Company;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use App\Forms\Form;
use Nette\Utils\ArrayHash;

class AdditionalControl extends BaseControl
{

	public $onSuccess = [];

	/** @var SignUpStorage @inject */
	public $session;

	/** @var UserFacade @inject */
	public $userFacade;
	
	public function render()
	{
		$template = $this->getTemplate();
		$template->role = $this->session->role;
		$template->roleCandidate = \App\FrontModule\Presenters\SignPresenter::ROLE_CANDIDATE;
		$template->roleCompany = \App\FrontModule\Presenters\SignPresenter::ROLE_COMPANY;
		parent::render();
	}

	/** @return Form */
	protected function createComponentCandidateForm()
	{
		$form = new Form();
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addText('fullName', 'Name:')
				->setAttribute('placeholder', 'name and surename')
				->setRequired('Please enter your name.');

		$form->addDatePicker('birthday', 'Birthday:')
				->setAttribute('placeholder', 'dd/mm/yyyy');

		$form->addSubmit('continue', 'Continue');

		$form->onSuccess[] = $this->candidateFormSucceeded;
		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function candidateFormSucceeded(Form $form, ArrayHash $values)
	{
		$this->session->user->name = $values->fullName;
		
		$this->presenter->redirect(':Front:Sign:up', [
			'step' => 'summary'
		]);
	}

	/** @return Form */
	protected function createComponentCompanyForm()
	{
		$form = new Form();
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addText('name', 'Comapny:')
				->setAttribute('placeholder', 'Company name')
				->setRequired('Please enter your company\'s name.');

		$form->addText('company_id', 'ID:')
				->setAttribute('placeholder', 'Company ID');

		$form->addTextArea('address', 'Address:')
				->setAttribute('placeholder', 'Company full address');

		$form->addSubmit('continue', 'Continue');

		$form->onSuccess[] = $this->companyFormSucceeded;
		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function companyFormSucceeded(Form $form, ArrayHash $values)
	{
		// Uložit data z formuláře.
		$company = new Company();
		$company->name = $values->name;
		$this->session->company = $company;
		
		$this->presenter->redirect(':Front:Sign:up', [
			'step' => 'summary'
		]);
	}

}

interface IAdditionalControlFactory
{

	/** @return AdditionalControl */
	function create();
}
