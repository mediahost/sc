<?php

namespace App\AppModule\Presenters;

use App\Components\Company\CompanyControl;
use App\Components\Company\ICompanyControlFactory;
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

	/** @var ICompanyControlFactory @inject */
	public $iCompanyControlFactory;

	/** @var ICompanyUserControlFactory @inject */
	public $iCompanyUserControlFactory;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var CompanyPermission */
	private $companyPermission;

	/** @var Company */
	private $company;

	// </editor-fold>

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->companyPermission = $this->companyPermission;
		$this->template->company = $this->company;
	}

	/**
	 * Check if user has any company to show and get company ID
	 * @param type $id
	 */
	private function checkCompanyId($id)
	{
		if (!count($this->user->identity->allowedCompanies)) {
			$this->flashMessage('You have no company to show', 'info');
			$this->redirect('wrongCompany');
		}
		$this->getCompany($id);
	}

	/**
	 * Get allowed company
	 * @param type $id
	 * @return Company
	 */
	private function getCompany($id)
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
		return $this->company;
	}

	/**
	 * @secured
	 * @resource('company')
	 * @privilege('default')
	 */
	public function actionDefault($id)
	{
		$this->checkCompanyId($id);
		$this['companyForm']->setEntity($this->company);
		$this['companyForm']->setCanEditInfo($this->companyPermission->isAllowed('info', 'edit'));
	}

	/**
	 * @secured
	 * @resource('company')
	 * @privilege('users')
	 */
	public function actionUsers($id)
	{
		$this->checkCompanyId($id);
		$this->template->addFilter('canEditUser', $this->canEditUser);
	}

	/**
	 * @secured
	 * @resource('company')
	 * @privilege('editUser')
	 */
	public function actionEditUser($userId = NULL, $companyId = NULL)
	{
		$this->getCompany($companyId);
		$this['editUserForm']->setCompany($this->company);
		if ($userId) {
			$user = $this->em->getDao(User::getClassName())->find($userId);
			if ($user && $this->canEditUser($user)) {
				$this['editUserForm']->setUser($user);
			}
		}
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

	/** @return CompanyControl */
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
