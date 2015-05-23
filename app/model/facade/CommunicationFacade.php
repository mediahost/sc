<?php

namespace App\Model\Facade;

use App\Model\Entity\Communication;
use App\Model\Entity\Company;
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
	 * @param $message
	 * @param User $sender
	 * @param User $receiver
	 * @param Company $senderCompany
	 * @param Company $receiverCompany
	 * @return Communication
	 * @throws \Exception
	 */
	public function startCommunication($message, User $sender, User $receiver = NULL, Company $senderCompany = NULL, Company $receiverCompany = NULL)
	{
		if (is_null($receiver) && is_null($receiverCompany)) {
		    throw new \Exception('specify at least on receiver entity'); // TODO: pouzit vlastnu exception
		}
		$communication = $this->findCommunicationOfPair($sender, $receiver, $senderCompany, $receiverCompany);
		if ($communication) {
			$this->addMessage($communication, $message, $sender, $senderCompany, FALSE);
		} else {
		    $communication = $this->createCommunication($message, $sender, $senderCompany, FALSE);
			$this->addContributor($communication, $receiver, $receiverCompany, FALSE);
		}
		$this->em->flush();
		return $communication;
	}

	/**
	 * @param $message
	 * @param User $user
	 * @param Company $company
	 * @param bool $flush
	 * @return Communication
	 */
	public function createCommunication($message, User $user, Company $company = NULL, $flush = TRUE)
	{
		$communication = new Communication();
		$this->em->persist($communication);
		$this->addMessage($communication, $message, $user, $company, FALSE);
		if ($flush) $this->em->flush();
		return $communication;
	}

	/**
	 * @param Communication $communication
	 * @param $text
	 * @param User $user
	 * @param Company $company
	 * @param bool $flush
	 * @return Message
	 * @throws \Exception
	 */
	public function addMessage(Communication $communication, $text, User $user, Company $company = NULL, $flush = TRUE)
	{
		$sender = $communication->getContributor($user, $company);
		if (!$sender) {
			$sender = $this->addContributor($communication, $user, $company, FALSE);
		}

		$message = new Message();
		$message->text = $text;
		$message->sender = $sender;
		$message->communication = $communication;
		$communication->addMessage($message);
		if ($flush) $this->em->flush();
		return $message;
	}

	/**
	 * @param Communication $communication
	 * @param User $user
	 * @param Company $company
	 * @param bool $flush
	 * @return Sender
	 * @throws \Exception
	 */
	public function addContributor(Communication $communication, User $user = NULL, Company $company = NULL, $flush = TRUE)
	{
		if (is_null($user) && is_null($communication)) {
			throw new \Exception('specify user or company'); // TODO: pouzit vlastnu exception
		}
		$contributor = new Sender();
		$contributor->user = $user;
		$contributor->communication = $communication;
		$contributor->company = $company;
		$this->em->persist($contributor);
		$communication->addContributor($contributor);
		if ($flush) $this->em->flush();
		return $contributor;
	}

	/**
	 * @param User $first
	 * @param User $second
	 * @param Company $firstCompany
	 * @param Company $secondCompany
	 * @return Communication|NULL
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function findCommunicationOfPair(User $first, User $second = NULL, Company $firstCompany = NULL, Company $secondCompany = NULL)
	{
		$queryBuilder = $this->em->createQueryBuilder();
		$queryBuilder
			->select('c')
			->from(Communication::getClassName(), 'c')
			->join('c.contributors', 's1')
			->join('c.contributors', 's2')
			->where('s1.user = ?0')
			->andWhere('s2.user ' . ($second ? '= ?2' : 'IS NULL'))
			->andWhere('s1.company ' . ($firstCompany ? '= ?1' : 'IS NULL'))
			->andWhere('s2.company ' . ($secondCompany ? '= ?3' : 'IS NULL'))
			->setParameter(0, $first);

		if ($firstCompany) $queryBuilder->setParameter(1, $firstCompany);
		if ($second) $queryBuilder->setParameter(2, $second);
		if ($secondCompany) $queryBuilder->setParameter(3, $secondCompany);

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
			->addSelect('MAX(m.time) as HIDDEN last_message_time')
			->join('c.contributors', 's')
			->join('c.messages', 'm')
			->where('s.user = ?0')
			->andWhere('s.company IS NULL')
			->orderBy('last_message_time', 'DESC')
			->groupBy('c')
			->setParameter(0, $user);
		$query = $queryBuilder->getQuery();
		return $query->getResult();
	}

	/**
	 * @param Company $company
	 * @return Communication[]
	 */
	public function getCompanyCommunications(Company $company)
	{
		$queryBuilder = $this->em->createQueryBuilder();
		$queryBuilder->select('c')
			->from(Communication::getClassName(), 'c')
			->addSelect('MAX(m.time) as HIDDEN last_message_time')
			->join('c.contributors', 's')
			->join('c.messages', 'm')
			->where('s.company = ?0')
			->orderBy('last_message_time', 'DESC')
			->groupBy('c')
			->setParameter(0, $company);
		$query = $queryBuilder->getQuery();
		return $query->getResult();
	}

	/**
	 * @param $id
	 * @return null|Communication
	 */
	public function getCommunication($id)
	{
		return $this->communicationRepository->find($id);
	}

	public function markCommunicationAsRead(Communication $communication, User $user, Company $company = NULL)
	{
		$sender = $communication->getContributor($user, $company);
		if (!$sender) {
		    $sender = $this->addContributor($communication, $user, $company);
		}
		foreach ($communication->messages as $message) {
			if (!$message->isReadBySender($sender)) {
			    $message->addRead(new Read($sender));
			}
		}
		$this->em->flush();

	}

}