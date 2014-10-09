<?php

namespace App\AdminModule\Presenters;

use App\Components\Sign;

/**
 * User Settings presenter.
 */
class UserSettingsPresenter extends BasePresenter
{

	/** @var \App\Model\Facade\AuthFacade @inject */
	public $authFacade;

	/** @var \App\Model\Facade\UserFacade @inject */
	public $userFacade;

	/** @var Sign\IAuthControlFactory @inject */
	public $iAuthControlFactory;

	/** @var \App\Components\IDeleteControlFactory @inject */
	public $iDeleteControlFactory;

	/** @var \App\Components\User\ISettingsControlFactory @inject */
	public $iSettingsControlFactory;
	
	/**
	 * @secured
	 * @resource('user_settings')
	 * @privilege('view')
	 */
	public function actionDefault()
	{
		
	}

	/**
	 * @secured
	 * @resource('user_settings')
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
	 * @resource('user_settings')
	 * @privilege('settings')
	 */
	public function actionSettings()
	{
		$this['auth']->setForce();
	}

// <editor-fold defaultstate="collapsed" desc="Components">

	/** @return Sign\AuthControl */
	protected function createComponentAuth()
	{
		return $this->iAuthControlFactory->create();
	}

	/** @return DeleteControl */
	protected function createComponentDelete()
	{
		return $this->iDeleteControlFactory->create();
	}
	
	/** @return \App\Components\User\SettingsControl */
	protected function createComponentSettings()
	{
		return $this->iSettingsControlFactory->create();
	}

// </editor-fold>
}
