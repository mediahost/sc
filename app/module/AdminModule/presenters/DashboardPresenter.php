<?php

namespace App\AdminModule\Presenters;

/**
 * Dashboard presenter.
 */
class DashboardPresenter extends BasePresenter
{

	/**
	 * @secured
	 * @resource('dashboard')
	 * @privilege('view')
	 */
	public function actionDefault()
	{

	}

}
