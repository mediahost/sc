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

	public function getSendersFromCandidates(array $candidates)
	{
		$userIds = [];
		foreach ($candidates as $candidate) {
			/** @var Candidate $candidate */
			$user = $candidate->person->user;
			$userIds[] = $user->id;
		}
		$criteria = [
			'user IN' => $userIds,
		];
		return $this->senderRepo->findAssoc($criteria, 'id');
	}

	public function createCommunication(Sender $sender, array $recipients, $subject, $message = NULL, Job $job = NULL, Candidate $candidate = NULL)
	{
		$recipients[] = $sender;
		$communication = new Communication($recipients);
		$communication->subject = $subject;
		if ($job) {
			$communication->job = $job;
		}
		if ($candidate) {
			$communication->candidate = $candidate;
		}
		if ($message) {
			$communication->addMessage($sender, $message);
			$this->communicationRepo->save($communication);
		}
		return $communication;
	}

	public function findCommunication(Sender $sender, array $recipients, $subject, Job $job = NULL, Candidate $candidate = NULL)
	{
		$recipients[] = $sender;
		return $this->communicationRepo->findOneByContributors($recipients, $subject, $job, $candidate);
	}

	public function findOrCreate(Sender $sender, array $recipients, $subject, Job $job = NULL, Candidate $candidate = NULL)
	{
		$communication = $this->findCommunication($sender, $recipients, $subject, $job, $candidate);
		if (!$communication) {
			$communication = $this->createCommunication($sender, $recipients, $subject, NULL, $job, $candidate);
		}
		return $communication;
	}

	public function sendMessage(Communication $communication, Sender $sender, $message, $state = Message::STATE_DEFAULT)
	{
		$communication->addMessage($sender, $message, $state);
		$this->communicationRepo->save($communication);
		return $communication;
	}

	public function sendMatchMessage(Match $match)
	{
		if ($match->adminApprovedAt < $match->candidateApprovedAt) {
			return $this->sendApplyMessage($match, TRUE);
		} else {
			return $this->sendApproveMessage($match, TRUE);
		}
	}

	public function sendApplyMessage(Match $match, $invitedBefore = FALSE)
	{
		$sender = $this->findSender($match->candidate->person->user);
		$recipient = $this->findSender($match->job->accountManager);

		if ($sender && $recipient) {
			$subject = $this->translator->translate('Apply to job \'%job%\'', NULL, ['job' => (string)$match->job]);
			$communication = $this->findOrCreate($sender, [$recipient], $subject, $match->job, $match->candidate);

			$message = 'I have applied for \'%job%\' position with \'%company%\'';
			$message = $this->translator->translate($message, NULL, ['job' => (string)$match->job, 'company' => (string)$match->job->company]);
			$state = Message::STATE_SYSTEM_JOB;
			return $this->sendMessage($communication, $sender, $message, $state);
		}
	}

	public function sendApproveMessage(Match $match, $appliedBefore = FALSE)
	{
		$sender = $this->findSender($match->job->accountManager);
		$recipient = $this->findSender($match->candidate->person->user);

		if ($sender && $recipient) {
			$subject = $this->translator->translate('Apply to job \'%job%\'', NULL, ['job' => (string)$match->job]);
			$communication = $this->findOrCreate($sender, [$recipient], $subject, $match->job, $match->candidate);

			if ($appliedBefore) {
				$message = 'I have approved you for job \'%job%\' position with \'%company%\'';
			} else {
				$message = 'I have invited you to apply for the \'%job%\' position with \'%company%\'';
			}
			$message = $this->translator->translate($message, NULL, ['job' => (string)$match->job, 'company' => (string)$match->job->company]);
			$state = Message::STATE_SYSTEM_JOB;
			return $this->sendMessage($communication, $sender, $message, $state);
		}
	}

	public function sendAcceptMessage(Match $match, $accept = TRUE)
	{
		$sender = $this->findSender($match->job->companyAccountManager);
		$recipient = $this->findSender($match->candidate->person->user);

		if ($sender && $recipient) {
			$subject = $this->translator->translate('Apply to job \'%job%\'', NULL, ['job' => (string)$match->job]);
			$communication = $this->findOrCreate($sender, [$recipient], $subject, $match->job, $match->candidate);

			if ($accept) {
				$message = 'I have accepted you for job \'%job%\' position with \'%company%\'';
			} else {
				$message = 'I have rejected you for job \'%job%\' position with \'%company%\'';
			}
			$message = $this->translator->translate($message, NULL, ['job' => (string)$match->job, 'company' => (string)$match->job->company]);
			$message .= "\n\n";
			$message .= $match->acceptReason;
			$state = Message::STATE_SYSTEM_JOB;
			return $this->sendMessage($communication, $sender, $message, $state);
		}
	}

	public function sendRejectMessage(Match $match)
	{
		return $this->sendAcceptMessage($match, FALSE);
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

	public function delete(Sender $sender)
	{
		$communications = $this->communicationRepo->findByContributors([$sender], FALSE);
		foreach ($communications as $communication) {
			$this->communicationRepo->delete($communication);
		}
		$this->senderRepo->delete($sender);
		return $this;
	}

}