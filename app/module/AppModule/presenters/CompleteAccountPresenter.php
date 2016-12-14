<?php

namespace App\AppModule\Presenters;

use App\Components\AfterRegistration\CompleteCompany;
use App\Components\AfterRegistration\CompleteCv;
use App\Components\AfterRegistration\CompletePerson;
use App\Components\AfterRegistration\ICompleteCandidateFactory;
use App\Components\AfterRegistration\ICompleteCompanyFactory;
use App\Components\AfterRegistration\ICompleteCvFactory;
use App\Components\AfterRegistration\ICompletePersonFactory;
use App\Mail\Messages\IVerificationMessageFactory;
use App\Model\Entity\Candidate;
use App\Model\Entity\Company;
use App\Model\Entity\Person;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;

class CompleteAccountPresenter extends BasePresenter
{

	/** @var ICompletePersonFactory @inject */
	public $iCompletePersonFactory;

	/** @var ICompleteCvFactory @inject */
	public $iCompleteCvFactory;

	/** @var ICompleteCandidateFactory @inject */
	public $iCompleteCandidateFactory;

	/** @var ICompleteCompanyFactory @inject */
	public $iCompleteCompanyFactory;

	/** @var IVerificationMessageFactory @inject */
	public $verificationMessage;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	protected function startup()
	{
		parent::startup();
		if (!$this->user->loggedIn) {
			$this->redirect(':Front:Sign:in');
		}
	}

	public function beforeRender()
	{
		parent::beforeRender();
		$this->template->isCandidate = $this->user->isInRole(Role::CANDIDATE);
		$this->template->isCompany = $this->user->isInRole(Role::COMPANY);
	}

	/**
	 * @secured
	 * @resource('registration')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		$user = $this->getUser();
		if ($user->isInRole(Role::CANDIDATE)) {
			$candidate = $user->getIdentity()->getCandidate();
			$this['completeCv']->setCandidate($candidate);
			if (!$candidate->id) {
				$userRepo = $this->em->getRepository(User::getClassName());
				$userRepo->save($user->getIdentity());
			}
			if ($candidate->isCompleted()) {
				$this->redirect('verify');
			}
		}
		if ($user->isInRole(Role::COMPANY)) {
			if ($this->company) {
				$this->redirect('verify');
			}
		}
	}

	/**
	 * @secured
	 * @resource('registration')
	 * @privilege('verify')
	 */
	public function actionVerify()
	{
		$identity = $this->getUser()->getIdentity();
		$person = $identity->getPerson();
		$candidate = $person->getCandidate();
		if ($identity->verificated) {
			if ($this->user->isInRole(Role::CANDIDATE)) {
				if (!$candidate->isCompleted()) {
					$message = $this->translator->translate('Your CV file is missing. Please fill this item!');
					$this->flashMessage($message);
					$this->redirect('default');
				}
				$message = $this->translator->translate('Your candidate account is complete. Enjoy your ride!');
				$this->flashMessage($message);
				$this->redirect(':App:Dashboard:');
			} else if ($this->user->isInRole(Role::COMPANY)) {
				if (!$this->company) {
					$this->redirect('default');
				}
				$message = $this->translator->translate('Your company account is complete. Enjoy your ride!');
				$this->flashMessage($message);
				$this->redirect(':App:Dashboard:');
			}
		}
	}

	/**
	 * @secured
	 * @resource('registration')
	 * @privilege('resendVerification')
	 */
	public function handleResendVerification()
	{
		$identity = $this->getUser()->getIdentity();
		if (!$identity->verificated) {
			$userRepo = $this->em->getRepository(User::getClassName());
			$this->userFacade->setVerification($identity);
			$userRepo->save($identity);

			// Send verification e-mail
			$message = $this->verificationMessage->create();
			$message->addParameter('link', $this->link('//:Front:Sign:verify', $identity->verificationToken));
			$message->addTo($identity->mail);
			$message->send();

			$message = $this->translator->translate('Verification mail has been send.');
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		}
	}

	// <editor-fold desc="components">

	/** @return CompleteCv */
	protected function createComponentCompleteCv()
	{
		$control = $this->iCompleteCvFactory->create();
		$control->onAfterSave[] = function (CompleteCv $control, Candidate $candidate) {
			if (!$candidate->getPerson()->getUser()->verificated) {
				$message = $this->translator->translate('Your data was saved. Please verify your mail!');
				$this->flashMessage($message, 'success');
				$this->redirect('verify');
			} else {
				$message = $this->translator->translate('Your candidate account is complete. Enjoy your ride!');
				$this->flashMessage($message, 'success');
				$this->redirect(':App:Dashboard:');
			}
		};
		return $control;
	}

	/** @return CompletePerson */
	protected function createComponentCompletePerson()
	{
		$control = $this->iCompletePersonFactory->create();
		$control->onSuccess[] = function (CompletePerson $control, Person $person) {
			if (!$person->getUser()->verificated) {
				$message = $this->translator->translate('Your data was saved. Please verify your mail!');
				$this->flashMessage($message, 'success');
				$this->redirect('verify');
			} else {
				$message = $this->translator->translate('Your candidate account is complete. Enjoy your ride!');
				$this->flashMessage($message, 'success');
				$this->redirect(':App:Dashboard:');
			}
		};
		return $control;
	}

	/** @return CompleteCompany */
	protected function createComponentCompleteCompany()
	{
		$control = $this->iCompleteCompanyFactory->create();
		$control->onSuccess[] = function (CompleteCompany $control, Company $company) {
			$message = $this->translator->translate('Your company account is complete. Enjoy your ride!');
			$this->flashMessage($message, 'success');
			$this->redirect(':App:Dashboard:');
		};
		return $control;
	}

	// </editor-fold>
}
