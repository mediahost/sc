<?php

namespace App\AppModule\Presenters;

/**
 * 
 */
class CandidatePresenter extends BasePresenter
{
	/**
	 * @secured
	 * @resource('candidate')
	 * @privilege('view')
	 */
	public function actionDefault()
	{
		
	}
}
