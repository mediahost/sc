<?php

namespace App\FrontModule\Presenters;

use App\Model\Entity\Company;

class CompanyProfilePresenter extends BasePresenter
{

	public function actionDefault($id)
	{
		$companyDao = $this->em->getDao(Company::getClassName());
		$company = $companyDao->find($id);
		if ($company) {
			$this->template->company = $company;
		} else {
			$message = $this->translator->translate('Requested company isn\'t exist. Pleas check your URL, or try find company on our pages.');
			$this->flashMessage($message, 'error');
			$this->redirect(':Front:Homepage:');
		}
	}

}
