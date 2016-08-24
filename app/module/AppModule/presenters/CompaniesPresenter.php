<?php

namespace App\AppModule\Presenters;

use App\Components\Company\CompanyImagesControl;
use App\Components\Company\CompanyInfoControl;
use App\Components\Company\ICompanyDataViewFactory;
use App\Components\Company\ICompanyImagesControlFactory;
use App\Components\Company\ICompanyInfoControlFactory;
use App\Components\Grids\Company\CompaniesGrid;
use App\Components\Grids\Company\ICompaniesGridFactory;
use App\Model\Entity\Company;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\CompanyFacade;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;

class CompaniesPresenter extends BasePresenter
{

	/** @var Company */
	private $company;

	// <editor-fold desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var ICompanyInfoControlFactory @inject */
	public $iCompanyInfoControlFactory;

	/** @var ICompanyImagesControlFactory @inject */
	public $iCompanyImagesControlFactory;

	/** @var ICompaniesGridFactory @inject */
	public $iCompaniesGridFactory;

	/** @var ICompanyDataViewFactory @inject */
	public $iCompanyDataViewFactory;

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var EntityDao */
	private $companyDao;

	/** @var EntityDao */
	private $userDao;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->companyDao = $this->em->getDao(Company::getClassName());
		$this->userDao = $this->em->getDao(User::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('companies')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		if (in_array(Role::COMPANY, $this->getUser()->getRoles())) {
			$companies = $this->companyFacade->findByUser($this->getUser());
		} else {
			$companies = $this->companyFacade->findAll();
		}
		$this['companyDataView']->setCompanies($companies);
	}

	/**
	 * @secured
	 * @resource('companies')
	 * @privilege('add')
	 */
	public function actionAdd()
	{
		$this->company = new Company();
		$this['companyInfoForm']->setCompany($this->company);
		$this->setView('edit');
	}

	/**
	 * @secured
	 * @resource('companies')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$this->company = $this->companyDao->find($id);
		if (!$this->company) {
			$message = $this->translator->translate('This company wasn\'t found.');
			$this->flashMessage($message, 'error');
			$this->redirect('default');
		} else {
			$this['companyInfoForm']->setCompany($this->company);
		}
	}

	public function renderEdit()
	{
		$this->template->company = $this->company;
	}

	/**
	 * @secured
	 * @resource('companies')
	 * @privilege('editImages')
	 */
	public function actionEditImages($id)
	{
		$this->company = $this->companyDao->find($id);
		if (!$this->company) {
			$message = $this->translator->translate('This company wasn\'t found.');
			$this->flashMessage($message, 'error');
			$this->redirect('default');
		} else {
			$this['companyImagesForm']->setCompany($this->company);
			$this->template->company = $this->company;
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
		$company = $this->companyDao->find($id);
		if (!$company) {
			$message = $this->translator->translate('Company wasn\'t found.');
			$this->flashMessage($message, 'danger');
		} else {
			$this->companyFacade->delete($company);
			$this->flashMessage('Company was deleted.', 'success');
		}
		$this->redirect('default');
	}


	// </editor-fold>
	// <editor-fold desc="forms">

	/** @return CompanyInfoControl */
	public function createComponentCompanyInfoForm()
	{
		$control = $this->iCompanyInfoControlFactory->create();
		$control->setUser($this->user);
		$control->onAfterSave = function (Company $saved) {
			$message = $this->translator->translate('Company \'%company%\' was successfully saved.', ['company' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('default');
		};
		return $control;
	}

	/** @return CompanyImagesControl */
	public function createComponentCompanyImagesForm()
	{
		$control = $this->iCompanyImagesControlFactory->create();
		$control->onAfterSave = function (Company $saved) {
			$message = $this->translator->translate('Images for company \'%company%\' was successfully saved.', ['company' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redirect('this');
//			$this->redirect('default');
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

	/** @return CompanyDataView */
	public function createComponentCompanyDataView()
	{
		$control = $this->iCompanyDataViewFactory->create();
		return $control;
	}

	// </editor-fold>
}
