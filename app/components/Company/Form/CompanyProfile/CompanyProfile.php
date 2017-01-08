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
	/** @var array */
	public $onAfterSave = [];

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var Company */
	private $company;


	protected function createComponentForm()
	{
		$form = new Form();
		$form->setRenderer(new MetronicFormRenderer());
		$form->setTranslator($this->translator);

		if ($this->isAjax) {
			$form->elementPrototype->class = 'ajax';
		}

		$form->addText('name', 'Company')
			->setAttribute('placeholder', 'Company name')
			->setRequired('Please enter your company\'s name.');

		$form->addText('companyId', 'Company ID')
			->setAttribute('placeholder', 'Company identification')
			->setRequired('Please enter company identification.');

		$form->addSubmit('save', 'Save');
		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values)
	{
		$this->company->name = $values->name;
		$this->company->companyId = $values->companyId;
		$this->em->getRepository(Company::getClassName())
			->save($this->company);
		$this->onAfterSave($this->company);
	}

	private function getDefaults()
	{
		$result = [];
		if ($this->company) {
			$result = [
				'id' => $this->company->id,
				'name' => $this->company->name,
				'companyId' => $this->company->companyId
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