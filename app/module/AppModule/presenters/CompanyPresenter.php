<?php

namespace App\AppModule\Presenters;

/**
 * 
 */
class CompanyPresenter extends BasePresenter
{
	/**
	 * @secured
	 * @resource('company')
	 * @privilege('view')
	 */
	public function actionDefault()
	{
		
	}
}
