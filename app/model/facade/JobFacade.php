<?php

namespace App\Model\Facade;

use App\Model\Entity\Cv;
use App\Model\Entity\Job;
use App\Model\Repository\CvRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class JobFacade extends Object
{

	/** @var EntityManager @inject */
	public $em;

	/** @var CvRepository */
	private $cvDao;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->cvDao = $this->em->getDao(Cv::getClassName());
	}

	public function findCvs(Job $job)
	{
		return $this->cvDao->findBySkillRequests($job->skillRequests->toArray());
	}

}
