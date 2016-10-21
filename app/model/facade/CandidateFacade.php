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
use Tracy\Debugger;

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

	public function matchIntern(Candidate $candidate, Job $job, $approve = TRUE)
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

}
