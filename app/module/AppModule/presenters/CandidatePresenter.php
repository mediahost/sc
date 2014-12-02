<?php

namespace App\AppModule\Presenters;

use App\Model\Entity\Candidate;
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
		if (!$user->candidate) {
			$candidate = new Candidate();
			$candidate->user = $user;
			$this->em->getDao(Candidate::getClassName())->save($candidate);
			$user->candidate = $candidate;
		}
		
		$this->template->candidate = $user->candidate;
	}
}
