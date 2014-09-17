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

	/** @var Sign\IAuthControlFactory @inject */
	public $iAuthControlFactory;

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
	 * @privilege('authorization')
	 */
	public function actionAuthorization()
	{
		$this['auth']->setForce();
	}

	/**
	 * @secured
	 * @resource('user_settings')
	 * @privilege('authorization')
	 */
	public function actionSetPassword()
	{
		
	}

// <editor-fold defaultstate="collapsed" desc="Components">

	/** @return Sign\AuthControl */
	protected function createComponentAuth()
	{
		return $this->iAuthControlFactory->create();
	}

// </editor-fold>
}
