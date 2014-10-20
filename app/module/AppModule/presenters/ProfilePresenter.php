<?php

namespace App\AppModule\Presenters;

use App\Components\Sign;

/**
 * 
 */
class ProfilePresenter extends BasePresenter
{
	// <editor-fold defaultstate="collapsed" desc="constants & properties">

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

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="actions">
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
		$this['auth']->setForce();
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="components">

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
