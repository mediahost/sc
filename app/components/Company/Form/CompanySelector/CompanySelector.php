<?php

namespace App\Components\Company;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\Bootstrap3FormRenderer;
use App\Model\Entity\Company;

class CompanySelector extends BaseControl
{

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSelect = [];

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new Bootstrap3FormRenderer());

		$companyRepo = $this->em->getRepository(Company::getClassName());
		$companies = $companyRepo->findPairs('name');
		$form->addSelect('company', 'Company', $companies);

		$form->addSubmit('select', 'Select');

		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$companyRepo = $this->em->getRepository(Company::getClassName());
		$company = $companyRepo->find($values->company);
		$this->onAfterSelect($company);
	}

}

interface ICompanySelectorFactory
{

	/** @return CompanySelector */
	function create();
}
