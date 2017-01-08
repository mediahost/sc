<?php

namespace App\AppModule\Presenters;

use App\Components\Company\CompanyProfile;
use App\Components\Company\ICompanyAddressFactory;
use App\Components\Company\ICompanyProfileFactory;
use App\Components\Company\IPhotoFactory;
use App\Model\Entity\Address;
use App\Model\Entity\Company;
use App\Model\Facade\UserFacade;

class CompanyProfilePresenter extends BasePresenter
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var ICompanyProfileFactory @inject */
	public $companyProfileFactory;

	/** @var ICompanyAddressFactory @inject */
	public $companyAddressFactory;

	/** @var IPhotoFactory @inject */
	public $photoFactory;

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
		$control = $this->companyProfileFactory->create()
			->setAjax(true, true)
			->setCompany($this->company);
		$control->onAfterSave = function (Company $saved) {
			$this->redrawControl('companyDetails');
		};
		return $control;
	}

	public function createComponentAddressForm()
	{
		$control = $this->companyAddressFactory->create()
			->setAjax(true, true)
			->setAddress($this->company->getAddress())
			->canEdit($this->canEdit);
		$control->onAfterSave = function (Address $saved) {
			if (!$this->company->address) {
				$this->company->address = $saved;
				$this->em->persist($this->company);
			}
			$this->redrawControl('companyDetails');
		};
		return $control;
	}

	public function createComponentPhotoForm()
	{
		$control = $this->photoFactory->create()
			->setCompany($this->company)
			->canEdit($this->canEdit);
		$control->onAfterSave = function (Company $saved) {
			$message = $this->translator->translate('Photo for \'%candidate%\' was successfully saved.', ['candidate' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redrawControl('companyDetails');
		};
		return $control;
	}

	// </editor-fold>
}