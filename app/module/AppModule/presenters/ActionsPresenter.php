<?php

namespace App\AppModule\Presenters;

use App\Components\Grids\Action\ActionsGrid;
use App\Components\Grids\Action\IActionsGridFactory;
use App\Model\Entity;
use Doctrine\ORM\EntityRepository;

class ActionsPresenter extends BasePresenter
{

	// <editor-fold desc="constants & variables">

	/** @var IActionsGridFactory @inject */
	public $iActionsGridFactory;

	/** @var EntityRepository */
	private $actionsRepo;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->actionsRepo = $this->em->getRepository(Entity\Action::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('actions')
	 * @privilege('default')
	 */
	public function renderDefault()
	{

	}

	// </editor-fold>
	// <editor-fold desc="forms">

	/** @return ActionsGrid */
	public function createComponentActionsGrid()
	{
		$control = $this->iActionsGridFactory->create();
		return $control;
	}
	// </editor-fold>

}
