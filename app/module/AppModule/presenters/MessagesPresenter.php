<?php

namespace App\AppModule\Presenters;

use App\Components\ICommunicationFactory;
use App\Components\ICommunicationListFactory;
use App\Components\IStartCommunicationModalFactory;
use App\Model\Entity\Communication;
use App\Model\Facade\UserFacade;

class MessagesPresenter extends BasePresenter
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var ICommunicationFactory @inject */
	public $communicationFactory;

	/** @var ICommunicationListFactory @inject */
	public $communicationListFactory;

	/** @var IStartCommunicationModalFactory @inject */
	public $startCommunicationModalFactory;

	/** @var Communication */
	protected $communication;

	public function actionDefault($id = NULL)
	{
		if ($id) {
			$this->communication = $this->communicationFacade->getCommunication($id);
			if (!$this->communication || !$this->communication->isUserAllowed($this->user->identity)) {
				$this->flashMessage('Requested conversation was\'t find.', 'danger');
				$this->redirect('this', NULL);
			}
			$this->template->conversation = $this->communication;
		}
	}

	public function createComponentStartCommunicationModal()
	{
	    $control = $this->startCommunicationModalFactory->create();
		$control->onSuccess[] = function (Communication $communication) {
			$this->redirect('default', $communication->id);
		};
		return $control;
	}


	public function createComponentCommunication()
	{
	    $control = $this->communicationFactory->create();
		$control->setCommunication($this->communication);
		return $control;
	}

	public function createComponentCommunicationList()
	{
		$communications = $this->getUserCommunications();
	    $control = $this->communicationListFactory->create();
		foreach ($communications as $communication) {
			$control->addCommunication($communication, $this->link('default', $communication->id));
		}
		$control->setActiveCommunication($this->communication);
		return $control;
	}

}