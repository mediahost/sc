<?php

namespace App\AppModule\Presenters;

use App\Components\Auth\ConnectManagerControl;
use App\Components\Auth\IConnectManagerControlFactory;
use App\Components\Auth\ISetPasswordControlFactory;
use App\Components\Auth\SetPasswordControl;
use App\Components\User\IPreferencesControlFactory;
use App\Components\User\PreferencesControl;
use App\Model\Entity;
use App\Model\Facade\UserFacade;

class ProfilePresenter extends BasePresenter
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var ISetPasswordControlFactory @inject */
	public $iSetPasswordControlFactory;

	/** @var IPreferencesControlFactory @inject */
	public $iPreferencesControlFactory;

	/** @var IConnectManagerControlFactory @inject */
	public $iConnectManagerControlFactory;

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
	 * @privilege('delete')
	 */
	public function actionDelete()
	{
		$this->userFacade->deleteById($this->user->id);
		$this->user->logout();
		$this->flashMessage('Your account has been deleted', 'success');
		$this->redirect(":Front:Homepage:");
	}

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('settings')
	 */
	public function actionSettings()
	{
		
	}

	// <editor-fold defaultstate="collapsed" desc="components">

	/** @return SetPasswordControl */
	protected function createComponentSetPassword()
	{
		$control = $this->iSetPasswordControlFactory->create();
		$control->setUser($this->user);
		$control->onSuccess[] = function () {
			$this->flashMessage('Password has been successfuly set!', 'success');
			$this->redirect(':App:Profile:settings#connect-manager');
		};
		return $control;
	}

	/** @return PreferencesControl */
	protected function createComponentSettings()
	{
		$control = $this->iPreferencesControlFactory->create();
		$control->onAfterSave = function ($savedLanguage) {
			$this->flashMessage('Your settings has been saved.', 'success');
			$this->redirect('this#personal-settings', [
				'lang' => $savedLanguage,
			]);
		};
		return $control;
	}

	/** @return ConnectManagerControl */
	protected function createComponentConnect()
	{
		$control = $this->iConnectManagerControlFactory->create();
		$control->setUser($this->userFacade->find($this->user->id));
		$control->setAppActivateRedirect($this->link('this#set-password'));
		$control->onSuccess[] = function (Entity\User $user, $type) {
			$message = new \App\TaggedString('%s was disconnected.', $type);
			$this->flashMessage($message, 'success');
			if (!$this->isAjax()) {
				$this->redirect('this#connect-manager');
			}
		};
		$control->onLastConnection[] = function () {
			$this->flashMessage('Last login method is not possible deactivate.', 'warning');
			if (!$this->isAjax()) {
				$this->redirect('this#connect-manager');
			}
		};
		$control->onInvalidType[] = function ($type) {
			$message = new \App\TaggedString('We can\'t find \'%s\' to disconnect.', $type);
			$this->flashMessage($message, 'error');
			if (!$this->isAjax()) {
				$this->redirect('this#connect-manager');
			}
		};
		return $control;
	}

	// </editor-fold>
}
