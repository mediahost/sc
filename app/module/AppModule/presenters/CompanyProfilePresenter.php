<?php

namespace App\AppModule\Presenters;

use App\Components\Company\CompanyProfile;
use App\Components\Company\ICompanyProfileFactory;
use App\Model\Entity\Company;
use App\Model\Facade\UserFacade;

class CompanyProfilePresenter extends BasePresenter
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var ICompanyProfileFactory @inject */
	public $companyProfileFactory;

	/** @var Company */
	private $companyEntity;

	/** @var bool */
	private $isMine;

	/** @var bool */
	private $canEdit;

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('default')
	 */
	public function actionDefault($id = NULL)
	{
		if ($this->company && !$id) {
			$this->redirect('this', ['id' => $this->company->id]);
		}

		if ($id) {
			$companyRepo = $this->em->getRepository(Company::getClassName());
			$this->companyEntity = $companyRepo->find($id);
			if ($this->companyEntity) {
				$this->isMine = $this->companyFacade->isAllowed($this->companyEntity, $this->user->identity);
				$this->canEdit = $this->isMine || $this->user->isAllowed('profile', 'edit-others');
			} else {
				$message = $this->translator->translate('Candidate wasn\'t found');
				$this->flashMessage($message, 'warning');
				$this->redirect('Dashboard:');
			}
		} else {
			$message = $this->translator->translate('Candidate wasn\'t found');
			$this->flashMessage($message, 'warning');
			$this->redirect('Dashboard:');
		}
	}

	public function renderDefault()
	{
		$this->template->isMine = $this->isMine;
		$this->template->canEdit = $this->canEdit;
		$this->template->companyEntity = $this->companyEntity;
	}

	// <editor-fold desc="components">

	/**  @return CompanyProfile */
	public function createComponentCompanyDetails()
	{
		$control = $this->companyProfileFactory->create();
		$control->setAjax(true, true);
		$control->setCompany($this->company);
		return $control;
	}

	// </editor-fold>
}