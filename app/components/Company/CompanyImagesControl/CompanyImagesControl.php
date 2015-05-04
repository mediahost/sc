<?php

namespace App\Components\Company;

use App\Components\BaseControl;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Company;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Nette\Utils\ArrayHash;

class CompanyImagesControl extends BaseControl
{

	/** @var Company */
	private $company;

	// <editor-fold desc="events">

	/** @var array */
	public $onAfterSave = [];

	// </editor-fold>
	// <editor-fold desc="injects">

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	// </editor-fold>

	/** @return Form */
	protected function createComponentForm()
	{
		$this->checkEntityExistsBeforeRender();

		$form = new Form();
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addUploadImageWithPreview('logo', 'Logo')
				->setPreview('/foto/200-150/' . $this->company->logo, $this->company->name)
				->setSize(200, 150)
				->addCondition(Form::FILLED)
				->addRule(Form::IMAGE, 'Logo must be valid image');

		$form->addSubmit('save', 'Save');

		$form->setDefaults($this->getDefaults());
		$form->onSuccess[] = $this->formSucceeded;
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$this->load($values);
		$this->save();
		$this->onAfterSave($this->company);
	}

	private function load(ArrayHash $values)
	{
		if ($values->logo->isImage()) {
			$this->company->logo = $values->logo;
		}
		return $this;
	}

	private function save()
	{
		$this->em->persist($this->company);
		$this->em->flush();
		return $this;
	}

	/** @return array */
	protected function getDefaults()
	{
		$values = [
			'logo' => $this->company->logo,
		];
		return $values;
	}

	private function checkEntityExistsBeforeRender()
	{
		if (!$this->company) {
			throw new CompanyControlException('Use setCompany(\App\Model\Entity\Company) before render');
		}
	}

	// <editor-fold desc="setters & getters">

	public function setCompany(Company $company)
	{
		$this->company = $company;
		return $this;
	}

	// </editor-fold>
}

interface ICompanyImagesControlFactory
{

	/** @return CompanyImagesControl */
	function create();
}
