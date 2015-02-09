<?php

namespace App\AppModule\Presenters;

use App\Model\Entity\Company;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;

/**
 * Jobs presenter.
 */
class JobsPresenter extends BasePresenter
{
	// <editor-fold defaultstate="expanded" desc="injects">

	/** @var EntityManager @inject */
	public $em;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var EntityDao */
	private $companyDao;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->companyDao = $this->em->getDao(Company::getClassName());
	}

	// <editor-fold defaultstate="expanded" desc="actions & renderers">

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('default')
	 */
	public function actionDefault($companyId)
	{
		$company = $this->companyDao->find($companyId);
		if ($company) {
			$this->template->company = $company;
		} else {
			$this->flashMessage('Finded company isn\'t exists.', 'warning');
			$this->redirect('Dashboard:');
		}
	}

	// </editor-fold>
}
