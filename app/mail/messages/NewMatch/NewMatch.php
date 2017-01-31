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
		$company = $job->company;
		$candidate = $match->candidate;

		$this->addTo($company->delegate->mail);

		$this->addParameter('job', $job);
		$this->addParameter('candidate', $candidate);
		return $this;
	}

}

interface INewMatchFactory
{

	/** @return NewMatch */
	public function create();
}
