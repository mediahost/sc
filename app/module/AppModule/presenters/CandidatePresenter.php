<?php

namespace App\AppModule\Presenters;

use App\Model\Entity\User;

/**
 * 
 */
class CandidatePresenter extends BasePresenter
{
	/**
	 * @secured
	 * @resource('candidate')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		$user = $this->em->getDao(User::getClassName())->find($this->user->id);
		$this->template->candidate = $user->candidate;
	}
}
