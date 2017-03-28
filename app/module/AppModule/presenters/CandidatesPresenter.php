<?php

namespace App\AppModule\Presenters;

class CandidatesPresenter extends BasePresenter
{

	/**
	 * @secured
	 * @resource('candidates')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		$this['candidatesList']->setRegisteredCandidates(TRUE);
	}

	/**
	 * @secured
	 * @resource('candidates')
	 * @privilege('unregistered')
	 */
	public function actionUnregistered()
	{
		$this['candidatesList']->setRegisteredCandidates(FALSE);
	}

}
