<?php

namespace App\AppModule\Presenters;

use App\Components\Company\CompanyAddress;
use App\Components\Company\CompanyImages;
use App\Components\Company\CompanyInfo;
use App\Components\Company\ICompanyAddressFactory;
use App\Components\Company\ICompanyImagesFactory;
use App\Components\Company\ICompanyInfoFactory;
use App\Components\Grids\Company\CompaniesGrid;
use App\Components\Grids\Company\ICompaniesGridFactory;
use App\Model\Entity\Address;
use App\Model\Entity\Company;
use App\Model\Entity\User;
use App\Model\Facade\CompanyFacade;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;

class CompaniesPresenter extends BasePresenter
{

	// <editor-fold desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var ICompanyInfoFactory @inject */
	public $iCompanyInfoFactory;

	/** @var ICompanyImagesFactory @inject */
	public $iCompanyImagesFactory;

	/** @var ICompaniesGridFactory @inject */
	public $iCompaniesGridFactory;

	/** @var ICompanyAddressFactory @inject */
	public $companyAddresFactory;

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var EntityDao */
	private $companyRepo;

	/** @var EntityDao */
	private $userRepo;

	/** @var Company */
	private $companyEntity;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->companyRepo = $this->em->getRepository(Company::getClassName());
		$this->userRepo = $this->em->getRepository(User::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('companies')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
	}

	/**
	 * @secured
	 * @resource('companies')
	 * @privilege('add')
	 */
	public function actionAdd()
	{
		$this->companyEntity = new Company();
		$this['companyInfoForm']->setCompany($this->companyEntity);
		$this->setView('edit');
	}

	/**
	 * @secured
	 * @resource('companies')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$this->companyEntity = $this->companyRepo->find($id);
		if ($this->companyEntity) {
			$this['companyInfoForm']->setCompany($this->companyEntity);
		} else {
			$message = $this->translator->translate('This company wasn\'t found.');
			$this->flashMessage($message, 'error');
			$this->redirect('default');
		}
	}

	public function renderEdit()
	{
		$this->template->companyEntity = $this->companyEntity;
	}

	/**
	 * @secured
	 * @resource('companies')
	 * @privilege('editImages')
	 */
	public function actionEditImages($id)
	{
		$this->companyEntity = $this->companyRepo->find($id);
		if (!$this->companyEntity) {
			$message = $this->translator->translate('This company wasn\'t found.');
			$this->flashMessage($message, 'error');
			$this->redirect('default');
		} else {
			$this['companyImagesForm']->setCompany($this->companyEntity);
			$this->template->companyEntity = $this->companyEntity;
		}
	}

	/**
	 * @secured
	 * @resource('companies')
	 * @privilege('view')
	 */
	public function actionView($id)
	{
		$message = $this->translator->translate('Not implemented yet.');
		$this->flashMessage($message, 'warning');
		$this->redirect('default');
	}

	/**
	 * @secured
	 * @resource('companies')
	 * @privilege('delete')
	 */
	public function actionDelete($id)
	{
		$company = $this->companyRepo->find($id);
		if (!$company) {
			$message = $this->translator->translate('Company wasn\'t found.');
			$this->flashMessage($message, 'danger');
		} else {
			$this->companyFacade->delete($company);
			$message = $this->translator->translate('Company was deleted.');
			$this->flashMessage($message, 'success');
		}
		$this->redirect('default');
	}


	// </editor-fold>
	// <editor-fold desc="forms">

	/** @return CompanyInfo */
	public function createComponentCompanyInfoForm()
	{
		$control = $this->iCompanyInfoFactory->create();
		$control->onAfterSave = function (Company $saved) {
			$message = $this->translator->translate('Company \'%company%\' was successfully saved.', ['company' => (string)$saved]);
			$this->flashMessage($message, 'success');
			if ($saved->isNew()) {
				$this->redirect('edit', $saved->id);
			} else {
				$this->redirect('default');
			}

		};
		return $control;
	}

	/** @return CompanyAddress */
	public function createComponentCompanyAddress()
	{
		$control = $this->companyAddresFactory->create()
			->setAjax(true, true)
			->setAddress($this->companyEntity->address);
		$control->onAfterSave = function (Address $saved) {
			if (!$this->companyEntity->address) {
				$this->companyEntity->address = $saved;
				$this->em->persist($this->companyEntity);
			}
			$this->redrawControl('companyAddress');
		};
		return $control;
	}

	/** @return CompanyImages */
	public function createComponentCompanyImagesForm()
	{
		$control = $this->iCompanyImagesFactory->create();
		$control->onAfterSave = function (Company $saved) {
			$message = $this->translator->translate('Images for company \'%company%\' was successfully saved.', ['company' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		};
		return $control;
	}

	// </editor-fold>
	// <editor-fold desc="grids">

	/** @return CompaniesGrid */
	public function createComponentCompaniesGrid()
	{
		$control = $this->iCompaniesGridFactory->create();
		return $control;
	}
	// </editor-fold>
}
