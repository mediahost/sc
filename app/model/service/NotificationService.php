<?php

namespace App\Model\Service;

use App\Extensions\Settings\SettingsStorage;
use App\Model\Entity\Message;
use App\Model\Entity\Sender;
use Kdyby\Translation\Translator;
use Nette\Mail\IMailer;
use Nette\Object;
use Nette\Utils\Validators;

class NotificationService extends Object
{

	/** @var SettingsStorage @inject */
	public $settings;

	/** @var IMailer @inject */
	public $mailer;

	/** @var Translator @inject */
	public $translator;

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
		        return $this->settings->modules->notifications->newMessage;
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
		$mail->setFrom($this->settings->modules->notifications->from);
		$mail->addTo($notificationReceiver->user->mail);
		$subject = $this->translator->translate('Source-code.com - new message notification');
		$mail->setSubject($subject);
		$body = $this->translator->translate('You have now message on source-code.com (%name%: %text%', [
			'name' => $message->sender->getName(),
			'text' => $message->text,
		]);
		$mail->setBody($body); // TODO: mail formating
		$this->mailer->send($mail);
	}

}