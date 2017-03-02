<?php

namespace App\Mail\Messages;

use App\Model\Entity\Match;
use App\Model\Entity\User;

class NewApply extends BaseMessage
{

	protected function beforeSend()
	{
		$this->setFrom($this->settings->mails->automatFrom, $this->settings->pageInfo->projectName);
		$this->setSubject($this->translator->translate('New apply'));
		parent::beforeSend();
	}

	public function setMatch(Match $match)
	{
		$job = $match->job;
		$candidate = $match->candidate;

		$this->addParameter('job', $job);
		$this->addParameter('candidate', $candidate);
		return $this;
	}

	public function setUser(User $user)
	{
		$this->addTo($user->mail);

		$user->setAccess('now + ' . $this->settings->expiration->linkAccess);
		$this->em->persist($user);
		$this->em->flush();

		$this->addParameter('token', $user->accessToken);
		return $this;
	}

}

interface INewApplyFactory
{

	/** @return NewApply */
	public function create();
}
