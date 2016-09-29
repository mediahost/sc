<?php

namespace App\AppModule\Presenters;

use App\Components\AfterRegistration\CompleteCandidate;
use App\Components\AfterRegistration\CompleteCandidatePreview;
use App\Components\AfterRegistration\ICompleteCandidateFactory;
use App\Components\AfterRegistration\ICompleteCandidatePreviewFactory;
use App\Components\Auth\ConnectManager;
use App\Components\Auth\IConnectManagerFactory;
use App\Components\Auth\ISetPasswordFactory;
use App\Components\Auth\SetPassword;
use App\Components\Candidate\IAddressFactory;
use App\Components\Candidate\IPhotoFactory;
use App\Components\Candidate\IProfileFactory;
use App\Components\Candidate\ISocialFactory;
use App\Components\Conversation\Form\ConversationList;
use App\Components\Conversation\Form\IConversationListFactory;
use App\Components\Cv\ILivePreviewFactory;
use App\Components\Cv\ISkillsFactory;
use App\Components\Cv\LivePreview;
use App\Components\Cv\Skills;
use App\Components\User\CareerDocs;
use App\Components\User\ICareerDocsFactory;
use App\Model\Entity;
use App\Model\Entity\Candidate;
use App\Model\Entity\Cv;
use App\Model\Facade\CantDeleteUserException;
use App\Model\Facade\CvFacade;
use App\Model\Facade\UserFacade;

class ProfilePresenter extends BasePresenter
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var ISetPasswordFactory @inject */
	public $iSetPasswordFactory;

	/** @var IConnectManagerFactory @inject */
	public $iConnectManagerFactory;

	/** @var ISkillsFactory @inject */
	public $iSkillsFactory;

	/** @var IPhotoFactory @inject */
	public $iPhotoFactory;

	/** @var IProfileFactory @inject */
	public $iProfileFactory;

	/** @var IAddressFactory @inject */
	public $iAddressFactory;

	/** @var ISocialFactory @inject */
	public $iSocialFactory;

	/** @var ILivePreviewFactory @inject */
	public $iLivePreviewFactory;

	/** @var ICareerDocsFactory @inject */
	public $iCareerDocsFactory;

	/** @var ICompleteCandidateFactory @inject */
	public $iCompleteCandidateFactory;

	/** @var ICompleteCandidatePreviewFactory @inject */
	public $completeCandidatePreview;

	/** @var IConversationListFactory @inject */
	public $iConversationListFactory;

	/** @var CvFacade @inject */
	public $cvFacade;

	/** @var Person */
	private $person;

	/** @var Candidate */
	private $candidate;

	/** @var Cv */
	private $cv;

	protected function startup()
	{
		parent::startup();
		$this->person = $this->getUser()->getIdentity()->getPerson();
		$this->candidate = $this->person->getCandidate();
		$this->cv = $this->candidate->getCv();
	}

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		$this->template->person = $this->person;
		$this->template->candidate = $this->candidate;
	}

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('settings')
	 */
	public function actionSettings()
	{
		$this->redirect('connectManager');
	}

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('settings')
	 */
	public function actionConnectManager()
	{

	}

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('settings')
	 */
	public function actionSetPassword()
	{

	}

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('delete')
	 */
	public function actionDelete()
	{

	}

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('delete')
	 */
	public function handleDelete()
	{
		try {
			$this->userFacade->deleteById($this->getUser()->id);
			$this->user->logout();
			$message = $this->translator->translate('Your account has been deleted');
			$this->flashMessage($message, 'success');
			$this->redirect(":Front:Homepage:");
		} catch (CantDeleteUserException $ex) {
			$message = $this->translator->translate('You can\'t delete account, because you are only one admin for your company.');
			$this->flashMessage($message, 'danger');
			$this->redirect("this");
		}
	}

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('notifications')
	 */
	public function actionNotifications()
	{

	}

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('notifications')
	 */
	public function handleNotifyChange($bool)
	{
		if (is_numeric($bool)) {
			$bool = (bool)$bool;
		} else {
			$bool = NULL;
		}
		$userDao = $this->em->getDao(Entity\User::getClassName());
		$user = $userDao->find($this->user->id);
		$user->beNotified = $bool;
		$this->em->flush();
		$this->redrawControl('notifiButtons');
	}

	// <editor-fold desc="components">

	/** @return SetPassword */
	protected function createComponentSetPassword()
	{
		$control = $this->iSetPasswordFactory->create();
		$control->setUser($this->user);
		$control->onSuccess[] = function () {
			$message = $this->translator->translate('Password has been successfuly set!');
			$this->flashMessage($message, 'success');
			$this->redirect('this');
		};
		return $control;
	}

	/** @return ConnectManager */
	protected function createComponentConnect()
	{
		$userDao = $this->em->getDao(Entity\User::getClassName());
		$control = $this->iConnectManagerFactory->create();
		$control->setUser($userDao->find($this->user->id));
		$control->setAppActivateRedirect($this->link('setPassword'));
		$control->onConnect[] = function ($type) {
			$message = $this->translator->translate('%type% was connected.', ['type' => $type]);
			$this->flashMessage($message, 'success');
			if (!$this->isAjax()) {
				$this->redirect('this');
			}
		};
		$control->onDisconnect[] = function (Entity\User $user, $type) {
			$message = $this->translator->translate('%type% was disconnected.', ['type' => $type]);
			$this->flashMessage($message, 'success');
			if (!$this->isAjax()) {
				$this->redirect('this');
			}
		};
		$control->onLastConnection[] = function () {
			$message = $this->translator->translate('Last login method is not possible deactivate.');
			$this->flashMessage($message, 'danger');
			if (!$this->isAjax()) {
				$this->redirect('this');
			}
		};
		$control->onInvalidType[] = function ($type) {
			$message = $this->translator->translate('We can\'t find \'%type%\' to disconnect.', ['type' => $type]);
			$this->flashMessage($message, 'danger');
			if (!$this->isAjax()) {
				$this->redirect('this');
			}
		};
		$control->onUsingConnection[] = function ($type) {
			$message = $this->translator->translate('Logged %type% account is using by another account.', ['type' => $type]);
			$this->flashMessage($message, 'danger');
			if (!$this->isAjax()) {
				$this->redirect('this');
			}
		};
		return $control;
	}

	/** @return Skills */
	public function createComponentSkillsForm()
	{
		$control = $this->iSkillsFactory->create();
		$control->setTemplateFile('overview');
		$control->onlyFilledSkills = true;
		$control->setCv($this->cv);
		$control->setAjax(TRUE, TRUE);
		return $control;
	}

	/** @return Photo */
	public function createComponentPhotoForm()
	{
		$control = $this->iPhotoFactory->create();
		$control->setPerson($this->person);
		$control->onAfterSave = function (Entity\Person $saved) {
			$message = $this->translator->translate('Photo for \'%candidate%\' was successfully saved.', ['candidate' => (string)$saved]);
			$this->flashMessage($message, 'success');
			$this->redrawControl('personalDetails');
		};
		return $control;
	}

	/** @return Profile */
	public function createComponentProfileForm()
	{
		$control = $this->iProfileFactory->create();
		$control->setPerson($this->person);
		$control->onAfterSave = function (Entity\Person $saved) {
			$this->redrawControl('personalDetails');
		};
		return $control;
	}

	/** @return Address */
	public function createComponentAddressForm()
	{
		$control = $this->iAddressFactory->create();
		$control->setPerson($this->person);
		$control->onAfterSave = function (Entity\Person $saved) {
			$this->redrawControl('personalDetails');
		};
		return $control;
	}

	/** @return Social */
	public function createComponentSocialForm()
	{
		$control = $this->iSocialFactory->create();
		$control->setPerson($this->person);
		$control->onAfterSave = function (Entity\Person $saved) {
			$this->redrawControl('socialLinks');
		};
		return $control;
	}

	/** @return LivePreview */
	public function createComponentCvPreview()
	{
		$control = $this->iLivePreviewFactory->create();
		$control->setScale(0.8, 0.8, 1);
		$control->setCv($this->cv);
		return $control;
	}

	/** @return CareerDocs */
	public function createComponentDocsForm()
	{
		$control = $this->iCareerDocsFactory->create();
		$control->setCandidate($this->candidate);
		$control->setTemplateFile('overView');
		return $control;
	}

	/** @return CompleteCandidate */
	protected function createComponentCompleteCandidate()
	{
		$control = $this->iCompleteCandidateFactory->create();
		$control->onSuccess[] = function (CompleteCandidate $control, Candidate $candidate) {
			$message = $this->translator->translate('Your data was saved.');
			$this->flashMessage($message, 'success');
			$this->redrawControl('interestedIn');
		};
		return $control;
	}

	/** @return CompleteCandidatePreview */
	protected function createComponentCompleteCandidatePreview()
	{
		$control = $this->completeCandidatePreview->create();
		return $control;
	}

	/** @return ConversationList */
	public function createComponentRecentMessages()
	{
		$control = $this->iConversationListFactory->create();
		$control->setSender($this->sender);
		$control->disableSearchBox();
		return $control;
	}

	// </editor-fold>
}