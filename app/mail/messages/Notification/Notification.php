<?php

namespace App\Mail\Messages;

use App\Model\Entity\Message;
use App\Model\Entity\Sender;
use App\Model\Entity\User;

class Notification extends BaseMessage
{

	protected function beforeSend()
	{
		$this->setFrom('noreply@source-code.com');
		parent::beforeSend();
	}

	public function setReciever(Sender $reciever)
	{
		$user = $reciever->user;
		$this->addTo($user->mail, $reciever->name);

		$user->setAccess('now + ' . $this->settings->expiration->linkAccess);
		$this->em->persist($user);

		$this->addParameter('token', $user->accessToken);
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
