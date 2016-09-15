<?php

namespace App\Listeners\Model\Entity;

use App\Mail\Messages\INotificationFactory;
use App\Model\Entity\Message;
use App\Model\Entity\Notification;
use App\Model\Entity\Sender;
use Doctrine\ORM\Events;
use Kdyby\Events\Subscriber;
use Nette\Object;

class MessageListener extends Object implements Subscriber
{

	/** @var INotificationFactory @inject */
	public $iNotificationMessage;

	public function getSubscribedEvents()
	{
		return array(
			Events::prePersist,
		);
	}

	// <editor-fold defaultstate="collapsed" desc="listeners redirectors">

	public function prePersist($params)
	{
		$this->sendNotification($params);
	}

	// </editor-fold>

	private function sendNotification(Message $message)
	{
		$communication = $message->communication;

		$senderForNotification = [];

		if ($communication->contributors) {
			/** @var Sender $contributor */
			foreach ($communication->contributors as $contributor) {
				if ($contributor->beNotified === TRUE) {
					$senderForNotification[$contributor->id] = $contributor;
				}
			}
		}

		if ($communication->notifications) {
			/** @var Notification $notification */
			foreach ($communication->notifications as $notification) {
				if ($notification->enabled === TRUE) {
					$senderForNotification[$notification->sender->id] = $notification->sender;
				} else if ($notification->enabled === FALSE) {
					unset($senderForNotification[$notification->sender->id]);
				}
			}
		}

		unset($senderForNotification[$message->sender->id]);

		foreach ($senderForNotification as $sender) {
			$notificationMessage = $this->iNotificationMessage->create();
			$notificationMessage->setReciever($sender);
			$notificationMessage->setMessage($message);
			$notificationMessage->send();
		}
	}

}
