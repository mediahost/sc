<?php

namespace App\AppModule\Presenters;

use App\Components\Profile\ConnectControl;
use App\Components\Profile\ISetPasswordControlFactory;
use App\Components\Profile\SetPasswordControl;
use App\Components\User\ISettingsControlFactory;
use App\Components\User\SettingsControl;
use App\Model\Facade\UserFacade;

class ProfilePresenter extends BasePresenter
{

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var ISetPasswordControlFactory @inject */
	public $iSetPasswordControlFactory;

	/** @var ISettingsControlFactory @inject */
	public $iSettingsControlFactory;

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('view')
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
		$this->userFacade->hardDelete($this->user->id);
		$this->user->logout();
		$this->redirect(":Front:Sign:In");
	}

	/**
	 * @secured
	 * @resource('profile')
	 * @privilege('settings')
	 */
	public function actionSettings()
	{
//		$this['auth']->setForce();
	}

	// <editor-fold defaultstate="collapsed" desc="components">

	/** @return SetPasswordControl */
	protected function createComponentSetPassword()
	{
		return $this->iSetPasswordControlFactory->create();
	}

	/** @return SettingsControl */
	protected function createComponentSettings()
	{
		return $this->iSettingsControlFactory->create();
	}

	/** @return ConnectControl */
//	protected function createComponentConnect()
//	{
//		return $this->iConnectControlFactory->create();
//	}

	// </editor-fold>
}
