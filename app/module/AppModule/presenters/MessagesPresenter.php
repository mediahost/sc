<?php

namespace App\AppModule\Presenters;

use App\Components\CommunicationDataView;
use App\Components\Conversation\Form\Conversation;
use App\Components\Conversation\Form\ConversationList;
use App\Components\Conversation\Form\IConversationFactory;
use App\Components\Conversation\Form\IConversationListFactory;
use App\Components\Conversation\Form\INewConversationFactory;
use App\Components\Conversation\Form\NewConversation;
use App\Components\ICommunicationDataViewFactory;
use App\Model\Entity\Communication;
use App\Model\Entity\Sender;
use App\Model\Facade\UserFacade;

class MessagesPresenter extends BasePresenter
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var INewConversationFactory @inject */
	public $iNewConversationFactory;

	/** @var IConversationFactory @inject */
	public $iConversationFactory;

	/** @var IConversationListFactory @inject */
	public $iConversationListFactory;

	/** @var ICommunicationDataViewFactory @inject */
	public $iCommunicationDataViewFactory;

	/** @var Communication */
	private $communication;

	/**
	 * @secured
	 * @resource('messages')
	 * @privilege('default')
	 */
	public function actionDefault($id = NULL)
	{
		if (!$id) {
			$communication = $this->sender->lastCommunication;
			if ($communication) {
				$this->redirect('this', ['id' => $communication->id]);
			}
		} else {
			$communicationRepo = $this->em->getRepository(Communication::getClassName());
			$communication = $communicationRepo->find($id);
			if ($communication && $communication->isContributor($this->sender)) {
				$this['conversation']->setSender($this->sender);
				$this['conversation']->setCommunication($communication);
				$this['conversationList']->setCommunication($communication);
				$this->template->conversation = $communication;
				$this->communicationFacade->markAsRead($communication, $this->sender);
			} else {
				$message = $this->translator->translate('Requested conversation was\'t find for you.');
				$this->flashMessage($message, 'danger');
				$this->redirect('this', NULL);
			}
		}
	}

	/**
	 * @secured
	 * @resource('messages')
	 * @privilege('browse')
	 */
	public function actionBrowse($id)
	{
		if ($id) {
			$communicationRepo = $this->em->getRepository(Communication::getClassName());
			$communication = $communicationRepo->find($id);
			if ($communication) {
				$this['conversation']->setSender($communication->firstContributor);
				$this['conversation']->setCommunication($communication);
				$this['conversation']->diableEdit();
				$this->template->conversation = $communication;
			} else {
				$message = $this->translator->translate('Requested conversation was\'t find.');
				$this->flashMessage($message, 'danger');
				$this->redirect('this', NULL);
			}
		} else {
			$this->redirect('default', NULL);
		}
	}

	public function handleNotifyChange($value)
	{
		$this->sender->beNotified = (bool)$value;
		$senderRepo = $this->em->getRepository(Sender::getClassName());
		$senderRepo->save($this->sender);
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

	/** @return NewConversation */
	public function createComponentNewConversation()
	{
		$control = $this->iNewConversationFactory->create();
		$control->setSender($this->sender);
		$control->onSend[] = function (Communication $communication) {
			$this->redirect('default', $communication->id);
		};
		return $control;
	}

	/** @return Conversation */
	public function createComponentConversation()
	{
		$control = $this->iConversationFactory->create();
		$control->setAjax(TRUE, FALSE);
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

	/** @return ConversationList */
	public function createComponentConversationList()
	{
		$control = $this->iConversationListFactory->create();
		$control->setSender($this->sender);
		return $control;
	}

	/** @return CommunicationDataView */
	public function createComponentCommunicationDataView()
	{
		$control = $this->iCommunicationDataViewFactory->create();
		return $control;
	}
}