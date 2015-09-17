<?php

namespace App\Components;

use App\Model\Entity\Company;
use App\Model\Entity\Message;
use App\Model\Entity\Sender;
use App\Model\Facade\CommunicationFacade;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Nette\Security\User;

class Communication extends BaseControl
{

	/** @var \App\Model\Entity\Communication */
	protected $communication;

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	/** @var EntityManager @inject */
	public $em;

	/** @var User @inject */
	public $user;

	/** @var Company */
	protected $company;

	/** @var ITranslator @inject */
	public $translator;

	/**
	 * @param \App\Model\Entity\Communication $communication
	 */
	public function setCommunication(\App\Model\Entity\Communication $communication)
	{
		$this->communication = $communication;
	}

	public function comunicateAsCompany(Company $company)
	{
		$this->company = $company;
	}

	public function render()
	{
		$this->template->communication = $this->communication;
		$this->template->viewer = $this->getViewerContributor();
		parent::render();
	}

	public function createComponentForm()
	{
	    $form = new Form();
		$form->setTranslator($this->translator);
		$form->addText('text');
		$form->addSubmit('send');
		$form->onSuccess[] = $this->processForm;
		return $form;
	}

	public function processForm(Form $form, $values)
	{
		$this->communicationFacade->addMessage($this->communication, $values->text, $this->user->identity, $this->company);
		$this->redirect('this');
	}

	public function isViewer(Sender $sender)
	{
		if ($this->company) {
		    return $sender->company && $sender->company->id == $this->company->id;
		} else {
			return $sender->user->id == $this->user->id;
		}
	}

	public function isMessageUnread(Message $message)
	{
		if ($this->company && $message->isReadByCompany($this->company)) {
		    return TRUE;
		} elseif (!$this->company && $message->isReadByUser($this->user->identity)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	public function isViewedByCompany()
	{
		return (bool) $this->company;
	}

	public function getViewerContributor()
	{
		foreach ($this->communication->contributors as $contributor) {
			if ($contributor->user && $contributor->user->id == $this->user->id) {
			    return $contributor;
			}
		}
	}

	public function handleNotifyChange($bool)
	{
		if (is_numeric($bool)) {
		    $bool = (bool) $bool;
		} else {
			$bool = NULL;
		}
		$viewer = $this->getViewerContributor();
		$viewer->beNotified = $bool;
		$this->redrawControl('notifiButtons');
	}

}

interface ICommunicationFactory
{

	/**
	 * @return Communication
	 */
	public function create();

}