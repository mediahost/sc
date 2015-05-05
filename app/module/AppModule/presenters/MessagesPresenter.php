<?php

namespace App\AppModule\Presenters;

use App\Components\ICommunicationFactory;
use App\Model\Entity\Communication;
use App\Model\Entity\User;
use App\Model\Facade\CommunicationFacade;
use App\Model\Facade\UserFacade;
use App\Model\Repository\UserRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Application\UI\Form;

class MessagesPresenter extends BasePresenter
{

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var EntityManager @inject */
	public $em;

	/** @var ICommunicationFactory @inject */
	public $communicationFactory;

	/** @var Communication */
	protected $communication;

	/**
	 * @secured
	 * @resource('messages')
	 */
	public function actionDefault()
	{
		$userRepository = $this->em->getDao(User::getClassName());
		$user = $userRepository->find($this->user->id);
		$this->template->userEntity = $user;
		$this->template->communications = $this->communicationFacade->getUserCommunications($user);
	}

	public function actionCommunication($id)
	{
		$userRepository = $this->em->getDao(User::getClassName());
		$user = $userRepository->find($this->user->id);

		$this->communication = $this->communicationFacade->get($id);
		if (!$this->communication) {
		    $this->error();
		}
		if (!$this->communication->isSender($user)) {
			$this->error();
		}
		$this->template->communiation = $this->communication;
	}

	public function createComponentStarCommunicationForm()
	{
		$users = $this->userFacade->getUsers();
		foreach ($users as $id => $user) {
			if ($id == $this->user->id) {
			    unset($users[$id]);
				break;
			}
		}

	    $form = new Form();
		$form->addSelect('user', 'User', $users);
		$form->addTextArea('message', 'Message');
		$form->addSubmit('send', 'Send');
		$form->onSuccess[] = $this->processForm;
		return $form;
	}

	public function processForm(Form $form, $values)
	{
		/** @var UserRepository $userRepository */
		$userRepository = $this->em->getDao(User::getClassName());
		$sender = $userRepository->find($this->user->id);
		$receiver = $userRepository->find($values->user);
		$communication = $this->communicationFacade->startCommunication($sender, $receiver, $values->message);
		$this->redirect('communication', $communication->id);
	}

	public function createComponentCommunication()
	{
	    $control = $this->communicationFactory->create();
		$control->setCommunication($this->communication);
		return $control;
	}

}