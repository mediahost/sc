<?php

namespace App\AppModule\Presenters;

use App\Model\Entity\Company;
use App\Model\Entity\CompanyPermission;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * 
 */
class CompanyPresenter extends BasePresenter
{

	/** @var CompanyPermission */
	private $companyPermission;

	/** @var Company */
	private $company;

	protected function startup()
	{
		parent::startup();
		if (!count($this->user->identity->allowedCompanies)) {
			$this->flashMessage('You have no company to show', 'info');
			$this->redirect('wrongCompany');
		}
		$this->getCompany($this->getParameter('id'));
	}
	
	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->companyPermission = $this->companyPermission;
		$this->template->company = $this->company;
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
		
	}

	/**
	 * @secured
	 * @resource('company')
	 * @privilege('users')
	 */
	public function actionUsers($id)
	{
		
	}

}
