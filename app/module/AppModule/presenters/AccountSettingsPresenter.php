<?php

namespace App\AppModule\Presenters;

use App\Components\Auth\ConnectManager;
use App\Components\Auth\IConnectManagerFactory;
use App\Components\Auth\ISetPasswordFactory;
use App\Components\Auth\SetPassword;
use App\Model\Entity\User;
use App\Model\Facade\Traits\CantDeleteUserException;
use App\Model\Facade\UserFacade;

class AccountSettingsPresenter extends BasePresenter
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var ISetPasswordFactory @inject */
	public $iSetPasswordFactory;

	/** @var IConnectManagerFactory @inject */
	public $iConnectManagerFactory;

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('settings')
	 */
	public function actionDefault()
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
	 * @privilege('notifications')
	 */
	public function actionNotifications()
	{

	}


	// <editor-fold desc="handlers">
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
	public function handleNotifyChange($bool)
	{
		if (is_numeric($bool)) {
			$bool = (bool)$bool;
		} else {
			$bool = NULL;
		}
		$userDao = $this->em->getDao(User::getClassName());
		$user = $userDao->find($this->user->id);
		$user->beNotified = $bool;
		$this->em->flush();
		$this->redrawControl('notifiButtons');
	}

	// </editor-fold>
	// <editor-fold desc="components">

	/** @return SetPassword */
	protected function createComponentSetPassword()
	{
		$control = $this->iSetPasswordFactory->create();
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
		$userDao = $this->em->getDao(User::getClassName());
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
		$control->onDisconnect[] = function (User $user, $type) {
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

	// </editor-fold>
}