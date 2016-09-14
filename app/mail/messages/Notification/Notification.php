<?php

namespace App\Mail\Messages;

use App\Model\Entity\Message;
use App\Model\Entity\Sender;

class Notification extends BaseMessage
{

	protected function beforeSend()
	{
		$this->setFrom('noreply@source-code.com');
		parent::beforeSend();
	}

	public function setReciever(Sender $reciever)
	{
		$this->addTo($reciever->user->mail, $reciever->name);
		return $this;
	}

	public function setMessage(Message $message)
	{
		$this->setSubject($this->translator->translate('New message from %from%', NULL, ['from' => $message->sender->name]));
		$this->addParameter('message', $message);
		$this->addParameter('sender', $message->sender);
		return $this;
	}

}

interface INotificationFactory
{

	/** @return Notification */
	public function create();
}
