<?php

namespace App\AppModule\Presenters;

use App\Model\Entity\Company;
use App\Model\Entity\Job;
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

	/** @var EntityDao */
	private $jobDao;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->companyDao = $this->em->getDao(Company::getClassName());
		$this->jobDao = $this->em->getDao(Job::getClassName());
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
			$this->flashMessage('Finded company isn\'t exists.', 'danger');
			$this->redirect('Dashboard:');
		}
	}

	/**
	 * @secured
	 * @resource('jobs')
	 * @privilege('showAll')
	 */
	public function actionShowAll()
	{
		$this->template->jobs = $this->jobDao->findAll();
	}

	// </editor-fold>
}
