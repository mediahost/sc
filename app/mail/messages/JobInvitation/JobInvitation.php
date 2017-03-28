<?php

namespace App\Mail\Messages;

use App\Model\Entity\Match;
use App\Model\Entity\User;

class JobInvitation extends BaseMessage
{

	private $subject = 'Job Invitation';

	protected function beforeSend()
	{
		$this->setSubject($this->subject);
		parent::beforeSend();
	}

	public function setMatch(Match $match)
	{
		$job = $match->job;
		$candidate = $match->candidate;

		$this->addTo($candidate->person->user->mail);

		$this->addParameter('job', $job);
		$this->addParameter('candidate', $candidate);
		return $this;
	}

	public function setSender(User $user)
	{
		$this->setFrom($user->mail, $user->person->getFullName());
		$this->addParameter('sender', $user);
		return $this;
	}

	public function setText($text, $subject = NULL)
	{
		$this->addParameter('mainText', $text);
		if ($subject) {
			$this->subject = $subject;
		}
		return $this;
	}

}

interface IJobInvitationFactory
{

	/** @return JobInvitation */
	public function create();
}
