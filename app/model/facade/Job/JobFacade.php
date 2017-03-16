<?php

namespace App\Model\Facade;

use App\Model\Entity\Action;
use App\Model\Entity\Candidate;
use App\Model\Entity\Communication;
use App\Model\Entity\Cv;
use App\Model\Entity\Job;
use App\Model\Entity\JobCategory;
use App\Model\Entity\JobType;
use App\Model\Entity\Match;
use App\Model\Facade\Traits\JobCategoryFacade;
use App\Model\Repository\CommunicationRepository;
use App\Model\Repository\CvRepository;
use App\Model\Repository\JobRepository;
use App\Model\Repository\MatchRepository;
use Doctrine\ORM\EntityRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class JobFacade extends Object
{
	use JobCategoryFacade;

	/** @var EntityManager @inject */
	public $em;

	/** @var CvRepository */
	private $cvRepo;

	/** @var JobRepository */
	private $jobRepo;

	/** @var EntityRepository */
	private $actionRepo;

	/** @var MatchRepository */
	private $matchRepo;

	/** @var CommunicationRepository */
	private $communicationRepo;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->cvRepo = $this->em->getRepository(Cv::getClassName());
		$this->jobRepo = $this->em->getRepository(Job::getClassName());
		$this->actionRepo = $this->em->getRepository(Action::getClassName());
		$this->matchRepo = $this->em->getRepository(Match::getClassName());
		$this->communicationRepo = $this->em->getRepository(Communication::getClassName());
	}

	public function findCvs(Job $job)
	{
		return $this->cvRepo->findBySkillRequests($job->skillRequests->toArray());
	}

	public function find($id)
	{
		return $this->jobRepo->find($id);
	}

	public function findJobType($idType)
	{
		$typeRepo = $this->em->getDao(JobType::getClassName());
		return $typeRepo->find($idType);
	}

	public function findOrCreateJobType($type)
	{
		$typeRepo = $this->em->getDao(JobType::getClassName());
		$entity = $typeRepo->findOneBy(['name' => $type]);
		if (!isset($entity)) {
			$entity = new JobType();
			$entity->name = $type;
			$typeRepo->save($entity);
		}
		return $entity;
	}

	public function getJobTypes()
	{
		$typeRepo = $this->em->getRepository(JobType::getClassName());
		return $typeRepo->findPairs('name');
	}

	public function getJobCategories()
	{
		$categoryRepo = $this->em->getRepository(JobCategory::getClassName());
		return $categoryRepo->findPairs('name');
	}

	public function getUnmatched(Candidate $candidate, $toString = FALSE)
	{
		$jobs = $this->jobRepo->findAll();
		$unmatched = [];
		foreach ($jobs as $job) {
			$match = $this->matchRepo->findOneBy([
				'job' => $job,
				'candidate' => $candidate,
				'adminApprove' => TRUE,
			]);
			if (!$match) {
				$unmatched[$job->id] = $toString ? (string)$job : $job;
			}
		}
		return $unmatched;
	}

	public function delete($id)
	{
		$job = $this->jobRepo->find($id);
		$actions = $this->actionRepo->findByJob($job);
		foreach ($actions as $action) {
			$this->actionRepo->delete($action);
		}
		$matches = $this->matchRepo->findByJob($job);
		foreach ($matches as $match) {
			$this->matchRepo->delete($match);
		}
		$communications = $this->communicationRepo->findByJob($job);
		foreach ($communications as $communication) {
			$this->communicationRepo->delete($communication);
		}
		$this->em->remove($job);
		$this->em->flush();
	}
}
