<?php

namespace App\Components;

use App\Forms\Form;
use App\Model\Entity\Company;
use App\Model\Entity\User;
use App\Model\Facade\CommunicationFacade;
use App\Model\Facade\UserFacade;
use App\Model\Repository\UserRepository;
use Kdyby\Doctrine\EntityDao;

class StartCommunicationModal extends BaseControl
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var \Nette\Security\User @inject */
	public $user;

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	/** @var callable[] */
	public $onSuccess = [];

	/** @var Company */
	protected $company;

	public function communicateAsCompany(Company $company)
	{
		$this->company = $company;
	}

	public function createComponentForm()
	{
		/** @var  EntityDao $companyRepository */
		$companyRepository = $this->em->getDao(Company::getClassName()); // TODO: odstarnit volanie nad repository
		$companies = $companyRepository->findPairs('name', 'id');

		$users = $this->userFacade->getUsers();

		$form = new Form();
		if ($this->company) {
			$selectItems = [
				'Company' => [],
			];
			foreach ($this->user->identity->getCompanies() as $company) {
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

		$sender = $this->user->identity;
		$senderCompany = NULL;
		if ($this->company && $values->me != 'user') {
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

		$this->onSuccess($communication);
	}

}

interface IStartCommunicationModalFactory
{

	/**
	 * @return StartCommunicationModal
	 */
	public function create();

}