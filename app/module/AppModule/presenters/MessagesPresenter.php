<?php

namespace App\AppModule\Presenters;

use App\Components\ICommunicationDataViewFactory;
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
    
    /** @var ICommunicationDataViewFactory @inject */
	public $communicationDataViewFactory;

	/** @var Communication */
	protected $communication;

	/** @var array */
	protected $communications;

	/**
	 * @secured
	 * @resource('messages')
	 * @privilege('default')
	 */
	public function actionDefault($id = NULL)
	{
		$this->communications = $this->getUserCommunications();
		if($id == null) {
			$this->communication = reset($this->communications);
		} else if ($id) {
			$this->communication = $this->communicationFacade->getCommunication($id);
			if (!$this->communication || !$this->communication->isUserAllowed($this->user->identity)) {
                if (!$this->user->isAllowed('messagesList')) {
                	$message = $this->translator->translate('Requested conversation was\'t find.');
                    $this->flashMessage($message, 'danger');
                    $this->redirect('this', NULL);
                }
			}
		}
		$this->template->conversation = $this->communication;
		if ($this->communication) {
			$this->communicationFacade->markCommunicationAsRead($this->communication, $this->user->identity);
		}
	}
    
    /**
	 * @secured
	 * @resource('messagesList')
	 * @privilege('default')
	 */
    public function actionMessagesList() {

    }

	public function createComponentStartCommunicationModal()
	{
	    $control = $this->startCommunicationModalFactory->create();
        foreach ($this->communications as $communication) {
            $control->addCommunication($communication);
        }
		$control->onSuccess[] = function (Communication $communication) {
			$this->redirect('default', $communication->id);
		};
		return $control;
	}


	public function createComponentCommunication()
	{
	    $control = $this->communicationFactory->create();
		$control->setCommunication($this->communication);
		$control->onAddMessage[] = function() {
			if ($this->isAjax()) {
				$this['communication']->redrawControl();
				$this['communicationList']->redrawControl();
			} else {
				$this->redirect('this');
			}
		};
		return $control;
	}

	public function createComponentCommunicationList()
	{
	    $control = $this->communicationListFactory->create();
		foreach ($this->communications as $communication) {
			$control->addCommunication($communication, $this->link('default', $communication->id));
		}
		$control->setActiveCommunication($this->communication);
		return $control;
	}

    public function createComponentCommunicationDataView() {
        $control = $this->communicationDataViewFactory->create();
        return $control;
    }
}