<?php

namespace App\Model\Facade;

use App\Model\Entity\Communication;
use App\Model\Entity\Company;
use App\Model\Entity\Message;
use App\Model\Entity\Read;
use App\Model\Entity\Sender;
use App\Model\Entity\User;
use App\Model\Repository\CommunicationRepository;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Nette\InvalidArgumentException;
use Nette\Object;

class CommunicationFacade extends Object
{

	/** @var EntityManager @inject */
	public $em;

	/** @var CommunicationRepository */
	private $communicationRepo;

	/** @var EntityRepository */
	private $messageRepo;

	/** @var EntityRepository */
	private $readRepo;

	/** @var EntityRepository */
	private $senderRepo;

	/** @var array */
	public $onNewMessage = [];

	function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->communicationRepo = $this->em->getDao(Communication::getClassName());
		$this->messageRepo = $this->em->getDao(Message::getClassName());
		$this->readRepo = $this->em->getDao(Read::getClassName());
		$this->senderRepo = $this->em->getDao(Sender::getClassName());
	}

	public function createSender(User $user, Company $company = NULL)
	{
		$sender = new Sender($user);
		if ($company) {
			$sender->company = $company;
		}
		return $this->senderRepo->save($sender);
	}

	public function findSenders(User $user, Company $company = NULL)
	{
		$criteria = [
			'user' => $user,
		];
		if ($company) {
			$criteria['company'] = $company;
		}
		return $this->senderRepo->findBy($criteria);
	}

	public function getSenders(Sender $me = NULL)
	{
		$criteria = [];
		if ($me) {
			$criteria['id !='] = $me->id;
		}
		return $this->senderRepo->findAssoc($criteria, 'id');
	}

	public function sendMessage(Sender $sender, $recipient, $message)
	{
		if ($recipient instanceof Communication) {
			$communication = $recipient;
		} else if ($recipient instanceof Sender) {
			$communication = $this->findByContributors($sender, $recipient);
			if (!$communication) {
				$communication = new Communication($sender, $recipient);
			}
		} else {
			throw new InvalidArgumentException();
		}

		$communication->addMessage($sender, $message);
		$this->communicationRepo->save($communication);
		return $communication;
	}

	public function findByContributors(Sender $one, Sender $two)
	{
		return $this->communicationRepo->findBySenders($one, $two);
	}

	public function markAsRead(Communication $communication, Sender $reader)
	{
		$communication->markMessagesAsRead($reader);
		$this->communicationRepo->save($communication);
	}

	public function findByFulltext(Sender $me, $text)
	{
		return $this->communicationRepo->findByFulltext($me, $text);
	}

}