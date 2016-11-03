<?php

namespace App\Model\Facade;

use App\Model\Entity\Candidate;
use App\Model\Entity\Communication;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
use App\Model\Entity\Match;
use App\Model\Entity\Message;
use App\Model\Entity\Read;
use App\Model\Entity\Sender;
use App\Model\Entity\User;
use App\Model\Repository\CommunicationRepository;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\EntityRepository;
use Kdyby\Translation\Translator;
use Nette\InvalidArgumentException;
use Nette\Object;

class CommunicationFacade extends Object
{

	/** @var EntityManager @inject */
	public $em;

	/** @var Translator @inject */
	public $translator;

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

	function __construct(EntityManager $em, Translator $translator)
	{
		$this->em = $em;
		$this->translator = $translator;
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

	public function findSender(User $user, Company $company = NULL)
	{
		$criteria = [
			'user' => $user,
		];
		if ($company) {
			$criteria['company'] = $company;
		}
		return $this->senderRepo->findOneBy($criteria);
	}

	public function getSenders(Sender $me = NULL)
	{
		$criteria = [];
		if ($me) {
			$criteria['id !='] = $me->id;
		}
		return $this->senderRepo->findAssoc($criteria, 'id');
	}

	public function sendMessage(Sender $sender, $recipient, $message, $state = Message::STATE_DEFAULT, Job $job = NULL, Candidate $candidate = NULL)
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

		$communication->addMessage($sender, $message, $state, $job, $candidate);
		$this->communicationRepo->save($communication);
		return $communication;
	}

	public function sendMatchMessage(Match $match)
	{
		if ($match->adminApprovedAt < $match->candidateApprovedAt) {
			return $this->sendApproveMessage($match, TRUE);
		} else {
			return $this->sendApplyMessage($match, TRUE);
		}
	}

	public function sendApplyMessage(Match $match, $invitedBefore = FALSE)
	{
		$sender = $this->findSender($match->candidate->person->user);
		$recipient = $this->findSender($match->job->accountManager);

		if ($sender && $recipient) {
			if ($invitedBefore) {
				$message = 'I just accept invitation for job \'%job%\'';
			} else {
				$message = 'I just apply for job \'%job%\'';
			}
			$message = $this->translator->translate($message, NULL, ['job' => (string)$match->job]);
			$state = Message::STATE_SYSTEM;
			return $this->sendMessage($sender, $recipient, $message, $state, $match->job, $match->candidate);
		}
	}

	public function sendApproveMessage(Match $match, $appliedBefore = FALSE)
	{
		$sender = $this->findSender($match->job->accountManager);
		$recipient = $this->findSender($match->candidate->person->user);

		if ($sender && $recipient) {
			if ($appliedBefore) {
				$message = 'I just approved you for job \'%job%\'';
			} else {
				$message = 'I just invited you for job \'%job%\'';
			}
			$message = $this->translator->translate($message, NULL, ['job' => (string)$match->job]);
			$state = Message::STATE_SYSTEM;
			return $this->sendMessage($sender, $recipient, $message, $state, $match->job, $match->candidate);
		}
	}

	public function findByContributors(Sender $one, Sender $two)
	{
		return $this->communicationRepo->findBySenders($one, $two);
	}

	public function findByFulltext(Sender $me, $text)
	{
		return $this->communicationRepo->findByFulltext($me, $text);
	}

	public function markAsRead(Communication $communication, Sender $reader)
	{
		$communication->markMessagesAsRead($reader);
		$this->communicationRepo->save($communication);
	}

}