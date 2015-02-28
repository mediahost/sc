<?php

namespace App\Model\Repository\Finders\JobRepository;

use App\Model\Entity\Job;
use App\Model\Entity\SkillKnow;
use App\Model\Entity\SkillKnowRequest;
use App\Model\Repository\Finders\Finder;
use Doctrine\Common\Collections\ArrayCollection;

class FinderJobsBySkillKnows extends Finder
{

	/** @var ArrayCollection */
	private $jobs;

	/** @var ArrayCollection */
	private $skillKnows;

	protected function init()
	{
		$this->jobs = new ArrayCollection;
		$this->skillKnows = new ArrayCollection;
	}

	public function addKnow(SkillKnow $know)
	{
		$this->skillKnows->add($know);
	}

	public function getResult()
	{
		$this->build();
		return $this->jobs;
	}

	protected function build()
	{
		foreach ($this->qb->getQuery()->getResult() as $job) {
			if ($this->isSkillsMatch($job)) {
				$this->jobs->add($job);
			}
		}
	}

	private function isSkillsMatch(Job $job)
	{
		$skillRequests = new ArrayCollection($job->skillRequests);
		$existsSomeKnowWhichFits = function ($k, SkillKnowRequest $request) {
			return $this->existsSomeKnowWhichFits($request);
		};
		return $skillRequests->forAll($existsSomeKnowWhichFits);
	}

	private function existsSomeKnowWhichFits(SkillKnowRequest $skillRequest)
	{
		$isSkillRequestSatisfied = function ($k, SkillKnow $skillKnow) use ($skillRequest) {
			return $skillRequest->isSatisfiedBy($skillKnow);
		};
		return $this->skillKnows->exists($isSkillRequestSatisfied);
	}

}
