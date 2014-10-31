<?php

namespace App\Components\Profile;

use App\Components\BaseControl;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Company;
use App\Model\Facade\UserFacade;
use App\Model\Storage\SignUpStorage;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

class AdditionalControl extends BaseControl
{

	public $onSuccess = [];

	/** @var SignUpStorage @inject */
	public $session;

	/** @var UserFacade @inject */
	public $userFacade;

	public function beforeRender()
	{
		$this->template->role = $this->session->role;
	}

	/** @return Form */
	protected function createComponentCandidateForm()
	{
		$form = new Form();
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		$form->addText('fullName', 'Full name:')
				->setAttribute('placeholder', 'Full name');

		$form->addText('shoeSize', 'Shoe size:')
				->setAttribute('placeholder', 'Shoe size')
				->addRule(Form::FLOAT, 'Shoe size must be a number.');

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

		$form->addText('name', 'Comapny name:')
				->setAttribute('placeholder', 'Company name')
				->setRequired('Please enter your company\'s name.');

		$form->addTextArea('address', 'Address:')
				->setAttribute('placeholder', 'Address');

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
