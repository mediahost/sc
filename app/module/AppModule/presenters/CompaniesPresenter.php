<?php

namespace App\AppModule\Presenters;

use App\Components\Company\CompanyControl;
use App\Components\Company\ICompanyControlFactory;
use App\Components\User\CompanyUserControl;
use App\Components\User\ICompanyUserControlFactory;
use App\Model\Entity\Company;
use App\Model\Entity\User;
use App\TaggedString;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;

/**
 * Companies presenter.
 */
class CompaniesPresenter extends BasePresenter
{
	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	/** @var EntityManager @inject */
	public $em;

	/** @var EntityDao */
	private $companyDao;

	/** @var EntityDao */
	private $userDao;

	/** @var ICompanyControlFactory @inject */
	public $iCompanyControlFactory;

	/** @var ICompanyUserControlFactory @inject */
	public $iCompanyUserControlFactory;

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
		$this->setView('edit');
	}

	/**
	 * @secured
	 * @resource('companies')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$company = $this->companyDao->find($id);
		if (!$company) {
			$this->flashMessage('This company wasn\'t found.', 'error');
			$this->redirect('default');
		} else {
			$this['companyForm']->setEntity($company);
			$this['editUserForm']->setCompany($company);
		}
	}

	public function renderEdit()
	{
		$this->template->isAdd = !$this['companyForm']->isEntityExists();
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
			$this->flashMessage('Company wasn\'t found.', 'warning');
		} else {
			$this->companyDao->delete($company);
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
		$user = $this->userDao->find($userId);
		if ($user) {
			$this['editUserForm']->setUser($user);
		}
		$company = $this->companyDao->find($companyId);
		if ($company) {
			$this['editUserForm']->setCompany($company);
		}
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="forms">

	/** @return CompanyControl */
	public function createComponentCompanyForm()
	{
		$control = $this->iCompanyControlFactory->create();
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
}