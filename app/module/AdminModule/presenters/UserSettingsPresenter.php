<?php

namespace App\AdminModule\Presenters;

/**
 * User Settings presenter.
 */
class UserSettingsPresenter extends BasePresenter
{

	/**
	 * @secured
	 * @resource('user_settings')
	 * @privilege('view')
	 */
	public function actionDefault()
	{
		
	}

}
