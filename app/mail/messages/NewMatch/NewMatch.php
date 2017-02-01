<?php

namespace App\Mail\Messages;

use App\Model\Entity\Match;

class NewMatch extends BaseMessage
{

	protected function beforeSend()
	{
		$this->setFrom($this->settings->mails->automatFrom, $this->settings->pageInfo->projectName);
		$this->setSubject($this->translator->translate('New match'));
		parent::beforeSend();
	}

	public function setMatch(Match $match)
	{
		$job = $match->job;
		$candidate = $match->candidate;
		$user = $job->companyAccountManager;

		$this->addTo($job->companyAccountManager->mail);

		$user->setAccess('now + ' . $this->settings->expiration->linkAccess);
		$this->em->persist($user);
		$this->em->flush();

		$this->addParameter('job', $job);
		$this->addParameter('candidate', $candidate);
		$this->addParameter('token', $user->accessToken);
		return $this;
	}

}

interface INewMatchFactory
{

	/** @return NewMatch */
	public function create();
}
