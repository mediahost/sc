<?php

namespace App\Model\Facade;

use App\Model\Entity\Candidate;
use App\Model\Entity\Job;
use App\Model\Entity\Match;
use App\Model\Repository\CandidateRepository;
use App\Model\Repository\JobRepository;
use App\Model\Repository\MatchRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class CandidateFacade extends Object
{

	/** @var EntityManager @inject */
	public $em;

	/** @var CandidateRepository */
	private $candidateRepo;

	/** @var JobRepository */
	private $jobRepo;

	/** @var MatchRepository */
	private $matchRepo;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->candidateRepo = $this->em->getRepository(Candidate::getClassName());
		$this->jobRepo = $this->em->getRepository(Job::getClassName());
		$this->matchRepo = $this->em->getRepository(Match::getClassName());
	}

	public function matchApply(Candidate $candidate, Job $job, $approve = TRUE)
	{
		return $this->match($candidate, $job, FALSE, $approve);
	}

	public function matchApprove(Candidate $candidate, Job $job, $approve = TRUE)
	{
		return $this->match($candidate, $job, TRUE, $approve);
	}

	private function match(Candidate $candidate, Job $job, $isIntern = TRUE, $approve = TRUE)
	{
		$match = $this->findMatch($candidate, $job);
		if (!$match) {
			$match = new Match($job, $candidate);
		}
		if ($isIntern) {
			$match->adminApprove = $approve;
		} else {
			$match->candidateApprove = $approve;
		}
		$this->matchRepo->save($match);

		return $this;
	}

	public function findMatch(Candidate $candidate, Job $job)
	{
		return $this->matchRepo->findOneBy([
			'job' => $job,
			'candidate' => $candidate,
		]);
	}

	public function isApproved(Candidate $candidate, Job $job)
	{
		return $this->isMatched($candidate, $job, TRUE, FALSE);
	}

	public function isApplied(Candidate $candidate, Job $job)
	{
		return $this->isMatched($candidate, $job, FALSE, TRUE);
	}

	public function isMatched(Candidate $candidate, Job $job, $checkIntern = TRUE, $checkApply = TRUE)
	{
		/** @var Match $match */
		$match = $this->findMatch($candidate, $job);
		if ($match) {
			$isApplied = $checkApply && $match->candidateApprove;
			$isApproved = $checkIntern && $match->adminApprove;
			if (!$checkApply) {
				return $isApproved;
			} else if (!$checkIntern) {
				return $isApplied;
			} else {
				return $match->candidateApprove && $match->adminApprove;
			}
		}
		return FALSE;
	}

	public function findAppliedJobs(Candidate $candidate, $approved = NULL)
	{
		$criteria = [
			'matches.candidate' => $candidate,
			'matches.candidateApprove' => TRUE,
		];
		if ($approved === TRUE || $approved === FALSE) {
			$criteria['matches.adminApprove'] = $approved;
		}
		return $this->jobRepo->findBy($criteria);
	}

	public function findApprovedJobs(Candidate $candidate, $applied = NULL)
	{
		$criteria = [
			'matches.candidate' => $candidate,
			'matches.adminApprove' => TRUE,
		];
		if ($applied === TRUE || $applied === FALSE) {
			$criteria['matches.candidateApprove'] = $applied;
		}
		return $this->jobRepo->findBy($criteria);
	}

	public function findMatchedJobs(Candidate $candidate)
	{
		$criteria = [
			'matches.candidate' => $candidate,
			'matches.candidateApprove' => TRUE,
			'matches.adminApprove' => TRUE,
		];
		return $this->jobRepo->findBy($criteria);
	}

}
