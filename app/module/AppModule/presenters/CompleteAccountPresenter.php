<?php

namespace App\AppModule\Presenters;

use App\Components\AfterRegistration\CompleteCandidateSecond;
use App\Components\AfterRegistration\CompleteCompany;
use App\Components\AfterRegistration\ICompleteCandidateFirstFactory;
use App\Components\AfterRegistration\ICompleteCandidateSecondFactory;
use App\Components\AfterRegistration\ICompleteCompanyFactory;
use App\Mail\Messages\IVerificationMessageFactory;
use App\Model\Entity\Candidate;
use App\Model\Entity\Company;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;

class CompleteAccountPresenter extends BasePresenter
{

	/** @var ICompleteCandidateFirstFactory @inject */
	public $iCompleteCandidateFirstFactory;

	/** @var ICompleteCandidateSecondFactory @inject */
	public $iCompleteCandidateSecondFactory;

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
		if ($this->user->isInRole(Role::CANDIDATE)) {
			if (!$this->user->identity->candidate) {
				$userRepo = $this->em->getRepository(User::getClassName());
				$this->user->identity->initCandidate();
				$userRepo->save($this->user->identity);
			}
			if ($this->user->identity->candidate->isRequiredPersonalFilled()) {
				$this->redirect('step2');
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
		$user = $this->user;
		$candidate = $user->identity->candidate;
		if ($user->isInRole(Role::CANDIDATE) && $candidate->isRequiredOtherFilled()) {
			$this->redirect('verify');
		}
	}

	/**
	 * @secured
	 * @resource('registration')
	 * @privilege('step2')
	 */
	public function actionVerify()
	{
		$user = $this->user;
		$candidate = $user->identity->candidate;
		if ($user->identity->verificated) {
			if (!$candidate->isRequiredPersonalFilled()) {
				$this->redirect('default');
			} elseif (!$candidate->isRequiredOtherFilled()) {
				$this->redirect('step2');
			}
			$message = $this->translator->translate('Your candidate account is complete. Enjoy your ride!');
			$this->flashMessage($message);
			$this->redirect(':App:Dashboard:');
		}
	}

	/**
	 * @secured
	 * @resource('registration')
	 * @privilege('resendVerification')
	 */
	public function handleResendVerification()
	{
		$user = $this->user->identity;
		if (!$user->verificated) {
			$userRepo = $this->em->getRepository(User::getClassName());
			$this->userFacade->setVerification($user);
			$userRepo->save($user);

			// Send verification e-mail
			$message = $this->verificationMessage->create();
			$message->addParameter('link', $this->link('//:Front:Sign:verify', $user->verificationToken));
			$message->addTo($user->mail);
			$message->send();

			$message = $this->translator->translate('Verification mail has been send.');
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		}
	}

	// <editor-fold desc="components">

	/** @return CompleteCandidateFirst */
	protected function createComponentCompleteCandidateFirst()
	{
		$control = $this->iCompleteCandidateFirstFactory->create();
		$control->onSuccess[] = function (CompleteCandidateFirst $control, Candidate $candidate) {
			$message = $this->translator->translate('Your data was saved. Your candidate account is almost complete.');
			$this->flashMessage($message, 'success');
			$this->redirect('step2');
		};
		return $control;
	}

	/** @return CompleteCandidateSecond */
	protected function createComponentCompleteCandidateSecond()
	{
		$control = $this->iCompleteCandidateSecondFactory->create();
		$control->setUserEntity($this->user->identity);
		$control->onSuccess[] = function (CompleteCandidateSecond $control, Candidate $candidate) {
			if (!$candidate->user->verificated) {
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
			$this->redirect(':App:Company:default', ['id' => $company->id]);
		};
		return $control;
	}

	// </editor-fold>
}
