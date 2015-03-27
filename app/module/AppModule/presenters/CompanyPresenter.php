<?php

namespace App\AppModule\Presenters;

use App\Components\Company\CompanyInfoControl;
use App\Components\Company\ICompanyInfoControlFactory;
use App\Components\User\CompanyUserControl;
use App\Components\User\ICompanyUserControlFactory;
use App\Model\Entity\Company;
use App\Model\Entity\CompanyPermission;
use App\Model\Entity\User;
use App\TaggedString;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * 
 */
class CompanyPresenter extends BasePresenter
{
	// <editor-fold defaultstate="collapsed" desc="inject">

	/** @var ICompanyInfoControlFactory @inject */
	public $iCompanyControlFactory;

	/** @var ICompanyUserControlFactory @inject */
	public $iCompanyUserControlFactory;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var Company */
	protected $company;

	/** @var CompanyPermission */
	protected $companyPermission;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$companyId = $this->getParameter('id');
		if ($companyId) {
			$this->setCompany($companyId);
		}
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->companyPermission = $this->companyPermission;
		$this->template->company = $this->company;
	}

	private function checkCompany($id)
	{
		if (!count($this->user->identity->allowedCompanies)) {
			$this->flashMessage('You have no company to show', 'info');
			$this->redirect('wrongCompany');
		}
		$this->setCompany($id);
	}

	private function setCompany($id)
	{
		if (!$this->company) {
			$allowedCompaniesCollection = new ArrayCollection($this->user->identity->allowedCompanies);
			$alowedCompany = $allowedCompaniesCollection->filter(function($permission) use ($id) {
				return $permission->user->id == $this->user->id && $permission->company->id == $id;
			});
			$this->companyPermission = $alowedCompany->current();
			if (!$this->companyPermission instanceof CompanyPermission) {
				$this->flashMessage('Requested ID isn\'t allowed for you. Select some company from menu.', 'info');
				$this->redirect('wrongCompany');
			}
			$this->company = $this->companyPermission->company;
		}
		return $this;
	}

	/**
	 * @secured
	 * @resource('company')
	 * @privilege('default')
	 * @companySecured
	 * @companyResource('info')
	 * @companyPrivilege('view')
	 */
	public function actionDefault($id)
	{
		$this->checkCompany($id);
		$this['companyForm']->setCompany($this->company);
		$this['companyForm']->setCanEditInfo($this->companyPermission->isAllowed('info', 'edit'));
	}

	/**
	 * @secured
	 * @resource('company')
	 * @privilege('users')
	 * @companySecured
	 * @companyResource('users')
	 * @companyPrivilege('view')
	 */
	public function actionUsers($id)
	{
		$this->checkCompany($id);
		$this->template->addFilter('canEditUser', $this->canEditUser);
	}

	/**
	 * @secured
	 * @resource('company')
	 * @privilege('editUser')
	 * @companySecured
	 * @companyResource('users')
	 * @companyPrivilege('edit')
	 */
	public function actionEditUser($userId = NULL, $companyId = NULL)
	{
		$this->setCompany($companyId);
		$this['editUserForm']->setCompany($this->company);
		if ($userId) {
			$user = $this->em->getDao(User::getClassName())->find($userId);
			if ($user && $this->canEditUser($user)) {
				$this['editUserForm']->setUser($user);
			}
		}
	}

	/**
	 * @secured
	 * @resource('company')
	 * @privilege('jobs')
	 * @companySecured
	 * @companyResource('jobs')
	 * @companyPrivilege('view')
	 */
	public function actionJobs($id)
	{
		$this->checkCompany($id);
		$this->template->addFilter('canEditUser', $this->canEditUser);
	}

	// <editor-fold defaultstate="expanded" desc="edit/delete priviledges">

	/**
	 * Decides if user can edit roles for user
	 * @param User $user
	 * @return boolean
	 */
	public function canEditUser(User $user)
	{
		return $this->companyPermission->isAllowed('users', 'edit') && $this->user->id != $user->id;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="forms">

	/** @return CompanyInfoControl */
	public function createComponentCompanyForm()
	{
		$control = $this->iCompanyControlFactory->create();
		$control->onAfterSave = function (Company $saved) {
			$message = new TaggedString('Company \'%s\' was successfully saved.', (string) $saved);
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		};
		return $control;
	}

	/** @return CompanyUserControl */
	public function createComponentEditUserForm()
	{
		$control = $this->iCompanyUserControlFactory->create();
		$control->onAfterSave = function (User $saved, Company $company) {
			$message = new TaggedString('User \'%s\' was successfully saved.', (string) $saved);
			$this->flashMessage($message, 'success');
			$this->redirect('users', $company->id);
		};
		return $control;
	}

	// </editor-fold>
}
