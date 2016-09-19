<?php

namespace App\Model\Facade;

use App\Model\Entity\Cv;
use App\Model\Entity\Job;
use App\Model\Repository\CvRepository;
use App\Model\Repository\JobRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class CvFacade extends Object
{

	/** @var EntityManager @inject */
	public $em;

	/** @var CvRepository */
	private $cvRepo;

	/** @var JobRepository */
	private $jobRepo;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->cvRepo = $this->em->getRepository(Cv::getClassName());
		$this->jobRepo = $this->em->getRepository(Job::getClassName());
	}

	public function findJobs(Cv $cv)
	{
		return $this->jobRepo->findBySkillKnows($cv->skillKnows);
	}

}
