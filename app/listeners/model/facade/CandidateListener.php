<?php

namespace App\Listeners\Model\Facade;

use App\Mail\Messages\INewApplyFactory;
use App\Mail\Messages\INewMatchFactory;
use App\Model\Entity\Match;
use App\Model\Entity\User;
use App\Model\Facade\CommunicationFacade;
use App\Model\Facade\UserFacade;
use Kdyby\Events\Subscriber;
use Nette\Object;

class CandidateListener extends Object implements Subscriber
{

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var INewMatchFactory @inject */
	public $iNewMatchFactory;

	/** @var INewApplyFactory @inject */
	public $iNewApplyFactory;

	public function getSubscribedEvents()
	{
		return [
			'App\Model\Facade\CandidateFacade::onMatch' => 'onMatch',
			'App\Model\Facade\CandidateFacade::onAccept' => 'onAccept',
		];
	}

	public function onMatch(Match $match)
	{
		if ($match->fullApprove) {
			$this->communicationFacade->sendMatchMessage($match);

			$notificationMessage = $this->iNewMatchFactory->create();
			$notificationMessage->setMatch($match);
			$notificationMessage->send();

		} else if ($match->candidateApprove) {
			$this->communicationFacade->sendApplyMessage($match);

			$users = $this->userFacade->getDealers();
			foreach ($users as $user) {
				$notificationMessage = $this->iNewMatchFactory->create();
				$notificationMessage->setMatch($match);
				$notificationMessage->setUser($user);
				$notificationMessage->send();
			}

		} else if ($match->adminApprove) {
			$this->communicationFacade->sendApproveMessage($match);
		}
	}

	public function onAccept(Match $match)
	{
		if ($match->accepted) {
			$this->communicationFacade->sendAcceptMessage($match);
		} else if ($match->rejected) {
			$this->communicationFacade->sendRejectMessage($match);
		}
	}

}
