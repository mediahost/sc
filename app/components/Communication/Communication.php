<?php

namespace App\Components;

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

	/** @var \App\Model\Entity\User */
	protected $viewer;

	/** @var ITranslator @inject */
	public $translator;

	/**
	 * @param \App\Model\Entity\Communication $communication
	 */
	public function setCommunication(\App\Model\Entity\Communication $communication)
	{
		$this->communication = $communication;
	}

	public function render()
	{
		$this->template->communication = $this->communication;
		$this->template->viewer = $this->getViewer();
		parent::render();
	}

	/**
	 * @return \App\Model\Entity\User
	 */
	public function getViewer()
	{
		if (!$this->viewer) {
			$userRepository = $this->em->getDao(\App\Model\Entity\User::getClassName());
			$this->viewer = $userRepository->find($this->user->id);
		}
		return $this->viewer;
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
		$this->communicationFacade->addMessage($this->communication, $values->text, $this->getViewer());
		$this->redirect('this');
	}

}

interface ICommunicationFactory
{

	/**
	 * @return Communication
	 */
	public function create();

}