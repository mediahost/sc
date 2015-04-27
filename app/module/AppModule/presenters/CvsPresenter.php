<?php

namespace App\AppModule\Presenters;

use App\Components\Grids\Cv\CvsGrid;
use App\Components\Grids\Cv\ICvsGridFactory;
use App\Model\Entity\Cv;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;

class CvsPresenter extends BasePresenter
{
	// <editor-fold defaultstate="expanded" desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var ICvsGridFactory @inject */
	public $iCvsGridFactory;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var EntityDao */
	private $cvDao;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->cvDao = $this->em->getDao(Cv::getClassName());
	}

	// <editor-fold defaultstate="expanded" desc="actions & renderers">

	/**
	 * @secured
	 * @resource('cvs')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		
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

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="grids">

	/** @return CvsGrid */
	public function createComponentCvsGrid()
	{
		$control = $this->iCvsGridFactory->create();
		return $control;
	}

	// </editor-fold>
}
