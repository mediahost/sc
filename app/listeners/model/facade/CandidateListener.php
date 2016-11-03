<?php

namespace App\Listeners\Model\Facade;

use App\Model\Entity\Match;
use App\Model\Facade\CommunicationFacade;
use Kdyby\Events\Subscriber;
use Nette\Object;

class CandidateListener extends Object implements Subscriber
{

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	public function getSubscribedEvents()
	{
		return [
			'App\Model\Facade\CandidateFacade::onMatch' => 'onMatch',
		];
	}

	public function onMatch(Match $match)
	{
		if ($match->fullApprove) {
			$this->communicationFacade->sendMatchMessage($match);
		} else if ($match->candidateApprove) {
			$this->communicationFacade->sendApplyMessage($match);
		} else if ($match->adminApprove) {
			$this->communicationFacade->sendApproveMessage($match);
		}
	}

}
