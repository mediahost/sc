<?php

namespace App\AppModule\Presenters;

/**
 * Home presenter
 */
class DashboardPresenter extends BasePresenter
{

	/**
	 * @secured
	 * @resource('dashboard')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		
	}

}
