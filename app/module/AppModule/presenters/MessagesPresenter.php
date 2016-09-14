<?php

namespace App\AppModule\Presenters;

use App\Components\CommunicationList;
use App\Components\ICommunicationDataViewFactory;
use App\Components\ICommunicationFactory;
use App\Components\ICommunicationListFactory;
use App\Components\INewCommunicationFactory;
use App\Components\NewCommunication;
use App\Model\Entity\Communication;
use App\Model\Facade\UserFacade;

class MessagesPresenter extends BasePresenter
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var INewCommunicationFactory @inject */
	public $iNewCommunicationFactory;

	/** @var ICommunicationFactory @inject */
	public $iCommunicationFactory;

	/** @var ICommunicationListFactory @inject */
	public $iCommunicationListFactory;

	/** @var ICommunicationDataViewFactory @inject */
	public $iCommunicationDataViewFactory;

	/**
	 * @secured
	 * @resource('messages')
	 * @privilege('default')
	 */
	public function actionDefault($id = NULL)
	{
		if (!$id) {
			$communication = $this->sender->getLastCommunication();
			$this->redirect('this', ['id' => $communication->id]);
		} else {
			$communicationRepo = $this->em->getRepository(Communication::getClassName());
			$communication = $communicationRepo->find($id);
			if ($communication && $communication->isContributor($this->sender)) {
				$this['communication']->setCommunication($communication);
				$this->template->conversation = $communication;
				$this->communicationFacade->markAsRead($communication, $this->sender);
			} else {
				$message = $this->translator->translate('Requested conversation was\'t find for you.');
				$this->flashMessage($message, 'danger');
				$this->redirect('this', NULL);
			}
		}
	}

	public function handleNotifyChange($value = NULL)
	{
		// TODO: implement
		$this->redrawControl('notifyButtons');
	}

	/**
	 * @secured
	 * @resource('messagesList')
	 * @privilege('default')
	 */
	public function actionMessagesList()
	{

	}

	/** @return NewCommunication */
	public function createComponentNewCommunication()
	{
		$control = $this->iNewCommunicationFactory->create();
		$control->setSender($this->sender);
		$control->onSend[] = function (Communication $communication) {
			$this->redirect('default', $communication->id);
		};
		return $control;
	}

	/** @return \App\Components\Communication */
	public function createComponentCommunication()
	{
		$control = $this->iCommunicationFactory->create();
		$control->setAjax(TRUE, FALSE);
		$control->setSender($this->sender);
		$control->onSend[] = function () {
			if ($this->isAjax()) {
				$this['communication']->redrawControl();
				$this['communicationList']->redrawControl();
			} else {
				$this->redirect('this');
			}
		};
		return $control;
	}

	/** @return CommunicationList */
	public function createComponentCommunicationList()
	{
		$control = $this->iCommunicationListFactory->create();
		$control->setSender($this->sender);
		return $control;
	}

	public function createComponentCommunicationDataView()
	{
		$control = $this->iCommunicationDataViewFactory->create();
		return $control;
	}
}