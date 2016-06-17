<?php

namespace App\AppModule\Presenters;

use App\Components\AfterRegistration\CompleteCandidateSecondControl;
use App\Components\AfterRegistration\ICompleteCandidateSecondControlFactory;
use App\Components\Auth\ConnectManagerControl;
use App\Components\Auth\IConnectManagerControlFactory;
use App\Components\Auth\ISetPasswordControlFactory;
use App\Components\Auth\SetPasswordControl;
use App\Components\Cv\ILivePreviewControlFactory;
use App\Components\Cv\LivePreviewControl;
use App\Components\Cv\ISkillsControlFactory;
use App\Components\Cv\SkillsControl;
use App\Components\Candidate\IPhotoControlFactory;
use App\Components\Candidate\IProfileControlFactory;
use App\Components\Candidate\IAddressControlFactory;
use App\Components\Candidate\ISocialControlFactory;
use App\Components\User\ICareerDocsControlFactory;
use App\Model\Entity;
use App\Model\Entity\Cv;
use App\Model\Entity\Candidate;
use App\Model\Facade\CantDeleteUserException;
use App\Model\Facade\UserFacade;
use App\Model\Facade\CvFacade;
use App\TaggedString;

class ProfilePresenter extends BasePresenter
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var ISetPasswordControlFactory @inject */
	public $iSetPasswordControlFactory;

	/** @var IConnectManagerControlFactory @inject */
	public $iConnectManagerControlFactory;

	/** @var ISkillsControlFactory @inject */
	public $iSkillsControlFactory;

	/** @var IPhotoControlFactory @inject */
	public $iPhotoControlFactory;

	/** @var IProfileControlFactory @inject */
	public $iProfileControlFactory;

	/** @var IAddressControlFactory @inject */
	public $iAddressControlFactory;

	/** @var ISocialControlFactory @inject */
	public $iSocialControlFactory;

	/** @var ILivePreviewControlFactory @inject */
	public $iLivePreviewControlFactory;

	/** @var ICareerDocsControlFactory @inject */
	public $iCareerDocsControlFactory;

	/** @var ICompleteCandidateSecondControlFactory @inject */
	public $iCompleteCandidateSecondControlFactory;

	/** @var CvFacade @inject */
	public $cvFacade;

	/** @var Cv */
	private $cv;

	/** @var Candidate */
	private $candidate;


	private function getCv()
	{
		if (!isset($this->cv)) {
			$candidate = $this->user->identity->candidate;
			$this->cv = $this->cvFacade->getDefaultCvOrCreate($candidate);
		}
		return $this->cv;
	}

	protected function startup()
	{
		parent::startup();
		$this->candidate = $this->user->identity->candidate;
	}

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('default')
	 */
	public function actionDefault()
	{

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
			$this->userFacade->deleteById($this->user->id);
			$this->user->logout();
			$this->flashMessage('Your account has been deleted', 'success');
			$this->redirect(":Front:Homepage:");
		} catch (CantDeleteUserException $ex) {
			$this->flashMessage('You can\'t delete account, because you are only one admin for your company.', 'danger');
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

	/** @return SetPasswordControl */
	protected function createComponentSetPassword()
	{
		$control = $this->iSetPasswordControlFactory->create();
		$control->setUser($this->user);
		$control->onSuccess[] = function () {
			$this->flashMessage('Password has been successfuly set!', 'success');
			$this->redirect('this');
		};
		return $control;
	}

	/** @return ConnectManagerControl */
	protected function createComponentConnect()
	{
		$userDao = $this->em->getDao(Entity\User::getClassName());
		$control = $this->iConnectManagerControlFactory->create();
		$control->setUser($userDao->find($this->user->id));
		$control->setAppActivateRedirect($this->link('setPassword'));
		$control->onConnect[] = function ($type) {
			$message = new TaggedString('%s was connected.', $type);
			$this->flashMessage($message, 'success');
			if (!$this->isAjax()) {
				$this->redirect('this');
			}
		};
		$control->onDisconnect[] = function (Entity\User $user, $type) {
			$message = new TaggedString('%s was disconnected.', $type);
			$this->flashMessage($message, 'success');
			if (!$this->isAjax()) {
				$this->redirect('this');
			}
		};
		$control->onLastConnection[] = function () {
			$this->flashMessage('Last login method is not possible deactivate.', 'danger');
			if (!$this->isAjax()) {
				$this->redirect('this');
			}
		};
		$control->onInvalidType[] = function ($type) {
			$message = new TaggedString('We can\'t find \'%s\' to disconnect.', $type);
			$this->flashMessage($message, 'danger');
			if (!$this->isAjax()) {
				$this->redirect('this');
			}
		};
		$control->onUsingConnection[] = function ($type) {
			$message = new TaggedString('Logged %s account is using by another account.', $type);
			$this->flashMessage($message, 'danger');
			if (!$this->isAjax()) {
				$this->redirect('this');
			}
		};
		return $control;
	}

	/** @return SkillsControl */
	public function createComponentSkillsForm()
	{
		$control = $this->iSkillsControlFactory->create();
		$control->setTemplateFile('overview');
		$control->onlyFilledSkills = true;
		$control->setCv($this->getCv());
		$control->setAjax(TRUE, TRUE);
		return $control;
	}

	/** @return PhotoControl */
	public function createComponentPhotoControl()
	{
		$control = $this->iPhotoControlFactory->create();
		$control->setCandidate($this->candidate);
		$control->onAfterSave = function (Candidate $saved) {
			$message = new TaggedString('Photo for \'%s\' was successfully saved.', (string)$saved);
			$this->flashMessage($message, 'success');
			$this->redrawControl('personalDetails');
		};
		return $control;
	}

	/** @return ProfileControl */
	public function createComponentProfileControl()
	{
		$control = $this->iProfileControlFactory->create();
		$control->setCandidate($this->candidate);
		$control->onAfterSave = function (Candidate $saved) {
			$this->redrawControl('personalDetails');
		};
		return $control;
	}

	/** @return AddressControl */
	public function createComponentAddressControl()
	{
		$control = $this->iAddressControlFactory->create();
		$control->setCandidate($this->candidate);
		$control->onAfterSave = function (Candidate $saved) {
			$this->redrawControl('personalDetails');
		};
		return $control;
	}

	/** @return SocialControl */
	public function createComponentSocialControl()
	{
		$control = $this->iSocialControlFactory->create();
		$control->setCandidate($this->candidate);
		$control->onAfterSave = function (Candidate $saved) {
			$this->redrawControl('socialLinks');
		};
		return $control;
	}

	/** @return LivePreviewControl */
	public function createComponentCvPreview()
	{
		$control = $this->iLivePreviewControlFactory->create();
		$control->setScale(0.8, 0.8, 1);
		$control->setCv($this->cv);
		return $control;
	}

	public function createComponentDocsControl()
	{
		$control = $this->iCareerDocsControlFactory->create();
		$control->setCandidate($this->candidate);
		$control->setTemplateFile('overView');
		return $control;
	}

	/** @return CompleteCandidateSecondControl */
	protected function createComponentCompleteCandidateSecond()
	{
		$control = $this->iCompleteCandidateSecondControlFactory->create();
		$control->onSuccess[] = function (CompleteCandidateSecondControl $control, Candidate $candidate) {
			$this->flashMessage('Your data was saved.', 'success');
			$this->redirect('this');
		};
		return $control;
	}

	// </editor-fold>
}


class ProfilePresenterException
{

}