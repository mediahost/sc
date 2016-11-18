<?php

namespace App\Components\AfterRegistration;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Company;
use App\Model\Facade\CompanyFacade;
use Nette\Forms\IControl;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

class CompleteCompany extends BaseControl
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
		$form->setRenderer(new Bootstrap3FormRenderer());
		$form->setTranslator($this->translator);

		$form->addText('name', 'Company')
			->setAttribute('placeholder', 'Company name')
			->setRequired('Please enter your company\'s name.');

		$form->addText('companyId', 'Company ID')
			->setAttribute('placeholder', 'Company identification')
			->setRequired('Please enter company identification.');

		$form->addTextArea('address', 'Address')
			->setAttribute('placeholder', 'Company full address')
			->setRequired();

		$form->addUpload('logo', 'Logo')
			->addRule(Form::IMAGE, 'Logo must be image')
			->setRequired();

		$form->addSubmit('confirm', 'Confirm')
			->getControlPrototype()->setClass('loadingOnClick');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		if (!$this->companyFacade->isUniqueName($values->name)) {
			$message = $this->translator->translate('\'%name%\' is already registered.', ['name' => $values->name]);
			$form['name']->addError($message);
		}
		if (!$this->companyFacade->isUniqueId($values->companyId)) {
			$message = $this->translator->translate('\'%name%\' is already registered.', ['name' => $values->companyId]);
			$form['companyId']->addError($message);
		}

		if (!$form->hasErrors()) {
			// create company with admin access
			$company = new Company();
			$company->name = $values->name;
			$company->companyId = $values->companyId;
			$company->address = $values->address;
			$company->logo = $values->logo;
			$createdCompany = $this->companyFacade->create($company, $this->user->identity);

			$this->onSuccess($this, $createdCompany);
		}
	}

}

interface ICompleteCompanyFactory
{

	/** @return CompleteCompany */
	function create();
}
