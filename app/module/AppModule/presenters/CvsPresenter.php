<?php

namespace App\AppModule\Presenters;

use App\Components\Cv\ICvDataViewFactory;
use App\Components\Grids\Cv\CvsGrid;
use App\Components\Grids\Cv\ICvsGridFactory;
use App\Model\Entity\Cv;
use App\Model\Entity\Job;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;

class CvsPresenter extends BasePresenter
{
	// <editor-fold desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var ICvDataViewFactory @inject */
	public $cvDataViewFactory;

	// </editor-fold>
	// <editor-fold desc="variables">

	/** @var EntityRepository */
	private $cvRepo;

	/** @var EntityRepository */
	private $jobRepo;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->cvRepo = $this->em->getRepository(Cv::getClassName());
		$this->jobRepo = $this->em->getRepository(Job::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('cvs')
	 * @privilege('default')
	 */
	public function actionDefault($jobId = NULL)
	{
		if ($jobId) {
			$job = $this->jobRepo->find($jobId);
			if ($job) {
				$this['cvDataView']->setJob($job);
			}
		}
	}

	/**
	 * @secured
	 * @resource('cvs')
	 * @privilege('delete')
	 */
	public function actionDelete($id)
	{
		$this->flashMessage('Not implemented yet.', 'warning');
		$this->redirect('default');
	}

	public function createComponentCvDataView()
	{
		$control = $this->cvDataViewFactory->create();
		return $control;
	}
}
