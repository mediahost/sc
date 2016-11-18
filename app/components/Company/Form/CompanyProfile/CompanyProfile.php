<?php

namespace App\Components\Company;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Company;
use App\Model\Facade\CompanyFacade;
use Nette\Utils\ArrayHash;

class CompanyProfile extends BaseControl
{
	/** @var Company */
	private $company;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	public function render()
	{
		$this->setTemplateFile('company');
		parent::render();
	}

	public function renderPreview()
	{
		$this->template->company = $this->company;
		$this->setTemplateFile('companyPreview');
		parent::render();
	}

	public function handleEdit()
	{
		$this->redrawControl('companyBlock');
	}

	protected function createComponentForm()
	{
		$form = new Form();
		$form->setRenderer(new MetronicFormRenderer());
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

		$form->addSubmit('confirm', 'Confirm');
		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$this->company->name = $values->name;
		$this->company->companyId = $values->companyId;
		$this->company->address = $values->address;
		$this->company->logo = $values->logo;
		$this->em->getRepository(Company::getClassName())
			->save($this->company);
	}

	private function getDefaults()
	{
		$result = [];
		if ($this->company) {
			$result = [
				'id' => $this->company->id,
				'name' => $this->company->name,
				'companyId' => $this->company->companyId,
				'address' => $this->company->address,
				'logo' => $this->company->logo
			];
		}
		return $result;
	}

	public function setCompany(Company $company)
	{
		$this->company = $company;
		return $this;
	}
}

interface ICompanyProfileFactory
{
	/** @return CompanyProfile*/
	public function create();
}