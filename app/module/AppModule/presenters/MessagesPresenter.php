<?php

namespace App\AppModule\Presenters;

use App\Components\ICommunicationFactory;
use App\Model\Entity\Communication;
use App\Model\Entity\Company;
use App\Model\Entity\User;
use App\Model\Facade\UserFacade;
use App\Model\Repository\UserRepository;
use Kdyby\Doctrine\EntityDao;
use Nette\Application\UI\Form;

class MessagesPresenter extends BasePresenter
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var ICommunicationFactory @inject */
	public $communicationFactory;

	/** @var Communication */
	protected $communication;

	/**
	 * @secured
	 * @resource('messages')
	 * @privilege('default')
	 */
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

	public function createComponentStarCommunicationForm()
	{
		/** @var  EntityDao $companyRepository */
		$companyRepository = $this->em->getDao(Company::getClassName()); // TODO: odstarnit volanie nad repository
		$companies = $companyRepository->findPairs('name', 'id');

		$users = $this->userFacade->getUsers();

	    $form = new Form();
		if ($this->user->isInRole('company')) {
			$userRepository = $this->em->getDao(User::getClassName()); // TODO: odstarnit volanie nad repository
			/** @var User $user */
			$user = $userRepository->find($this->user->id);
			$selectItems = [
				'User' => [
					'user' => $this->user->identity->mail,
				],
				'Company' => [],
			];
			foreach ($user->getCompanies() as $company) {
				$selectItems['Company'][$company->id] = $company->name;
			}
		    $form->addSelect('me', 'as', $selectItems);
		}
		$select = $form->addSelect('type', 'with', ['user' => 'User', 'company' => 'Company'])
			->setDefaultValue('user');
		$userSelect = $form->addSelect('user', 'User', $users);
		$companySelect = $form->addSelect('company', 'Company', $companies);
		$form->addTextArea('message', 'Message');
		$form->addSubmit('send', 'Send');

		$select->addCondition(Form::EQUAL, 'user')
			->toggle($userSelect->getHtmlId().'-pair');
		$select->addCondition(Form::EQUAL, 'company')
			->toggle($companySelect->getHtmlId().'-pair');

		$form->onSuccess[] = $this->processForm;
		return $form;
	}

	public function processForm(Form $form, $values)
	{

		/** @var UserRepository $userRepository */
		$userRepository = $this->em->getDao(User::getClassName()); // TODO: odstarnit volanie nad repository
		/** @var EntityDao $userRepository */
		$companyRepository = $this->em->getDao(Company::getClassName()); // TODO: odstarnit volanie nad repository


		/** @var User $sender */
		$sender = $userRepository->find($this->user->id);
		$senderCompany = NULL;
		if ($this->user->isInRole('company') && $values->me != 'user') {
			/** @var Company $senderCompany */
		    $senderCompany = $companyRepository->find($values->me);
		}

		$receiver = NULL;
		$receiverCompany = NULL;
		if ($values->type == 'user') {
			/** @var User $receiver */
			$receiver = $userRepository->find($values->user);
		} else {
			$receiverCompany = $companyRepository->find($values->company);
		}


		$communication = $this->communicationFacade
			->startCommunication($values->message, $sender, $receiver, $senderCompany, $receiverCompany);
		$this->redirect('default', $communication->id);
	}

	public function createComponentCommunication()
	{
	    $control = $this->communicationFactory->create();
		$control->setCommunication($this->communication);
		return $control;
	}

}