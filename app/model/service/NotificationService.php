<?php

namespace App\Model\Service;

use App\Model\Entity\Message;
use App\Model\Entity\Sender;
use App\Model\Entity\Special\UniversalDataEntity;
use Nette\Mail\IMailer;
use Nette\Object;
use Nette\Utils\Validators;

class NotificationService extends Object
{

	/** @var UniversalDataEntity */
	private $settings;

	/** @var IMailer */
	private $mailer;

	public function __construct(UniversalDataEntity $settings, IMailer $mailer)
	{
		$this->settings = $settings;
		$this->mailer = $mailer;
	}

	public function processNewMessageNotifications(Message $message)
	{
		$notifiedUsers = [];
		foreach ($message->communication->contributors as $contributor) {
			if (!$contributor->user) {
			    continue;
			}
			if ($message->sender->user->id == $contributor->user->id) {
			    continue;
			}
			if (isset($notifiedUsers[$contributor->user->id])) {
				continue;
			}
			if ($this->shouldBeSenderNotified($contributor)) {
				$this->sendNewMessageNotification($message, $contributor);
				$notifiedUsers[$contributor->user->id] = $contributor->user;
			}
		}
	}

	public function shouldBeSenderNotified(Sender $sender)
	{
		if ($sender->beNotified === NULL) {
		    if ($sender->user->beNotified === NULL) {
		        return $this->settings->newMessage;
		    } else {
				return $sender->user->beNotified;
			}
		} else {
			return $sender->beNotified;
		}
	}

	public function sendNewMessageNotification(Message $message, Sender $notificationReceiver)
	{
		if (!Validators::isEmail($notificationReceiver->user->mail)) {
		    return;
		}
		$mail = new \Nette\Mail\Message();
		$mail->setFrom($this->settings->from);
		$mail->addTo($notificationReceiver->user->mail);
		$mail->setSubject('Source-code.com - new message notification');
		$mail->setBody('You have now message on source-code.com ('.$message->sender->getName().': '.$message->text.')'); // TODO: mail formating
		$this->mailer->send($mail);
	}

}