<?php

namespace App\AppModule\Presenters;

use App\Components\AfterRegistration\CompleteCandidate;
use App\Components\AfterRegistration\CompleteCompany;
use App\Components\AfterRegistration\CompletePerson;
use App\Components\AfterRegistration\ICompletePersonFactory;
use App\Components\AfterRegistration\ICompleteCandidateFactory;
use App\Components\AfterRegistration\ICompleteCompanyFactory;
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
			if (!$candidate->id) {
				$userRepo = $this->em->getRepository(User::getClassName());
				$userRepo->save($user->getIdentity());
			}
			if ($user->getIdentity()->getPerson()->isFilled()) {
				$this->redirect('step2');
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
	 * @privilege('step2')
	 */
	public function actionStep2()
	{
		$user = $this->getUser();
		$candidate = $user->getIdentity()->getCandidate();
		if ($user->isInRole(Role::CANDIDATE) && $candidate->isFilled()) {
			$this->redirect('verify');
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
				if (!$person->isFilled()) {
					$this->redirect('default');
				} elseif (!$candidate->isFilled()) {
					$this->redirect('step2');
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

	/** @return CompletePerson */
	protected function createComponentCompletePerson()
	{
		$control = $this->iCompletePersonFactory->create();
		$control->onSuccess[] = function (CompletePerson $control, Person $person) {
			$message = $this->translator->translate('Your data was saved. Your candidate account is almost complete.');
			$this->flashMessage($message, 'success');
			$this->redirect('step2');
		};
		return $control;
	}

	/** @return CompleteCandidate */
	protected function createComponentCompleteCandidate()
	{
		$control = $this->iCompleteCandidateFactory->create();
		$control->onSuccess[] = function (CompleteCandidate $control, Candidate $candidate) {
			if (!$candidate->getUser()->verificated) {
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
