<?php

namespace App\AppModule\Presenters;

use App\Components\Company\CompanyImagesControl;
use App\Components\Company\CompanyInfoControl;
use App\Components\Company\ICompanyImagesControlFactory;
use App\Components\Company\ICompanyInfoControlFactory;
use App\Components\Grids\Company\CompaniesGrid;
use App\Components\Grids\Company\ICompaniesGridFactory;
use App\Components\User\CompanyUserControl;
use App\Components\User\ICompanyUserControlFactory;
use App\Model\Entity\Company;
use App\Model\Entity\User;
use App\Model\Facade\CompanyFacade;
use App\TaggedString;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;

class CompaniesPresenter extends BasePresenter
{

	/** @var Company */
	private $company;

	// <editor-fold defaultstate="expanded" desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var ICompanyInfoControlFactory @inject */
	public $iCompanyInfoControlFactory;

	/** @var ICompanyImagesControlFactory @inject */
	public $iCompanyImagesControlFactory;

	/** @var ICompanyUserControlFactory @inject */
	public $iCompanyUserControlFactory;

	/** @var ICompaniesGridFactory @inject */
	public $iCompaniesGridFactory;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

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

	// <editor-fold defaultstate="expanded" desc="actions & renderers">

	/**
	 * @secured
	 * @resource('companies')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		$this->template->companies = $this->companyDao->findAll();
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
			$this->flashMessage('This company wasn\'t found.', 'error');
			$this->redirect('default');
		} else {
			$this['companyInfoForm']->setCompany($this->company);
			$this['editUserForm']->setCompany($this->company);
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
			$this->flashMessage('This company wasn\'t found.', 'error');
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
		$this->flashMessage('Not implemented yet.', 'warning');
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
			$this->flashMessage('Company wasn\'t found.', 'danger');
		} else {
			$this->companyFacade->delete($company);
			$this->flashMessage('Company was deleted.', 'success');
		}
		$this->redirect('default');
	}

	/**
	 * @secured
	 * @resource('companies')
	 * @privilege('editUser')
	 */
	public function actionEditUser($userId = NULL, $companyId = NULL)
	{
		if ($userId) {
			$user = $this->userDao->find($userId);
			if ($user) {
				$this['editUserForm']->setUser($user);
			}
		}
		if ($companyId) {
			$company = $this->companyDao->find($companyId);
			if ($company) {
				$this['editUserForm']->setCompany($company);
			}
		}
		$this['editUserForm']->onAfterSave = function (User $saved) {
			$message = new TaggedString('User \'%s\' was successfully saved.', (string) $saved);
			$this->flashMessage($message, 'success');
			$this->redirect('default');
		};
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="forms">

	/** @return CompanyInfoControl */
	public function createComponentCompanyInfoForm()
	{
		$control = $this->iCompanyInfoControlFactory->create();
		$control->setCanEditInfo($this->user->isAllowed('company', 'edit'));
		$control->setCanEditUsers($this->user->isAllowed('company', 'edit'));
		$control->setLinkAddUser($this->link('this#addUser'), ['data-toggle' => 'modal']);
		$control->onAfterSave = function (Company $saved) {
			$message = new TaggedString('Company \'%s\' was successfully saved.', (string) $saved);
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
			$message = new TaggedString('Images for company \'%s\' was successfully saved.', (string) $saved);
			$this->flashMessage($message, 'success');
			$this->redirect('this');
//			$this->redirect('default');
		};
		return $control;
	}

	/** @return CompanyUserControl */
	public function createComponentEditUserForm()
	{
		$control = $this->iCompanyUserControlFactory->create();
		$control->onAfterSave = function (User $saved) {
			$message = new TaggedString('User \'%s\' was successfully saved.', (string) $saved);
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		};
		return $control;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="grids">

	/** @return CompaniesGrid */
	public function createComponentCompaniesGrid()
	{
		$control = $this->iCompaniesGridFactory->create();
		return $control;
	}

	// </editor-fold>
}
