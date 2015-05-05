<?php

namespace App\Model\Facade;

use App\Model\Entity\Communication;
use App\Model\Entity\Message;
use App\Model\Entity\Read;
use App\Model\Entity\Sender;
use App\Model\Entity\User;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class CommunicationFacade extends Object
{

	/** @var EntityManager */
	private $em;

	/** @var EntityDao */
	protected $communicationRepository;

	/** @var EntityDao */
	protected $messageRepository;

	/** @var EntityDao */
	protected $readRepository;

	/** @var EntityDao */
	protected $senderRepository;

	function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->communicationRepository = $this->em->getDao(Communication::getClassName());
		$this->messageRepository = $this->em->getDao(Message::getClassName());
		$this->readRepository = $this->em->getDao(Read::getClassName());
		$this->senderRepository = $this->em->getDao(Sender::getClassName());
	}

	/**
	 * @param User $sender
	 * @param User $receiver
	 * @param $message
	 * @return Communication|NULL
	 */
	public function startCommunication(User $sender, User $receiver, $message)
	{
		$communication = $this->findCommunicationOfPair($sender, $receiver);
		if (!$communication) {
		    $communication = $this->createCommunication($sender, $receiver);
		}
		$this->addMessage($communication, $sender, $message);
		$this->em->flush();
		return $communication;
	}

	/**
	 * @param User $first
	 * @param User $second
	 * @return Communication
	 */
	public function createCommunication(User $first, User $second)
	{
		$communication = new Communication();
		$this->em->persist($communication);

		$this->addSender($communication, $first);
		$this->addSender($communication, $second);
		return $communication;
	}

	/**
	 * @param Communication $communication
	 * @param User $user
	 * @param $text
	 * @return Message
	 */
	public function addMessage(Communication $communication, User $user, $text, $flush = TRUE)
	{
		$sender = $communication->getSender($user);
		if (!$sender) {
			$sender = $this->addSender($communication, $user);
		}

		$message = new Message();
		$message->text = $text;
		$message->sender = $sender;
		$message->communication = $communication;
		$communication->addMessage($message);
		$this->em->flush();
		return $message;
	}

	/**
	 * @param Communication $communication
	 * @param User $user
	 * @return Sender
	 */
	public function addSender(Communication $communication, User $user)
	{
		$sender = new Sender();
		$sender->user = $user;
		$sender->communication = $communication;
		$this->em->persist($sender);
		$communication->addSender($sender);
		return $sender;
	}

	/**
	 * @param User $first
	 * @param User $second
	 * @return Communication|NULL
	 */
	public function findCommunicationOfPair(User $first, User $second)
	{
		$queryBuilder = $this->em->createQueryBuilder();
		$queryBuilder->select('c')
			->from(Communication::getClassName(), 'c')
			->join('c.contributors', 's1')
			->join('c.contributors', 's2')
			->where('s1.user = ?0')
			->andWhere('s2.user = ?1')
			->setParameter(0, $first)
			->setParameter(1, $second);
		$query = $queryBuilder->getQuery();
		return $query->getOneOrNullResult();
	}

	/**
	 * @param User $user
	 * @return Communication[]
	 */
	public function getUserCommunications(User $user)
	{
		$queryBuilder = $this->em->createQueryBuilder();
		$queryBuilder->select('c')
			->from(Communication::getClassName(), 'c')
			->join('c.contributors', 's')
			->where('s.user = ?0')
			->setParameter(0, $user);
		$query = $queryBuilder->getQuery();
		return $query->getResult();
	}

	/**
	 * @param $id
	 * @return null|Communication
	 */
	public function get($id)
	{
		return $this->communicationRepository->find($id);
	}

}