<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Company;
use App\Model\Facade\CompanyFacade;
use Kdyby\Doctrine\EntityManager;
use Nette\Forms\IControl;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

class CompleteCompanyControl extends BaseControl
{

	// <editor-fold desc="events">

	/** @var array */
	public $onSuccess = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var User @inject */
	public $user;

	// </editor-fold>

	public function render()
	{
		$this->setTemplateFile('company');
		parent::render();
	}

	protected function createComponentForm()
	{
		$form = new Form();
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

		$form->onSuccess[] = $this->formSucceeded;
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

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		// create company with admin access
		$company = new Company();
		$company->name = $values->name;
		$company->companyId = $values->companyId;
		$company->address = $values->address;
		$createdCompany = $this->companyFacade->create($company, $this->user->identity);

		$this->onSuccess($this, $createdCompany);
	}

}

interface ICompleteCompanyControlFactory
{

	/** @return CompleteCompanyControl */
	function create();
}
