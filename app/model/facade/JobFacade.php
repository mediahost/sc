<?php

namespace App\Model\Facade;

use App\Model\Entity\Cv;
use App\Model\Entity\Job;
use App\Model\Repository\CvRepository;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

/**
 * TODO: Test it
 */
class JobFacade extends Object
{

	/** @var EntityManager @inject */
	public $em;

	/** @var EntityDao */
	private $jobDao;

	/** @var CvRepository */
	private $cvDao;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->jobDao = $this->em->getDao(Job::getClassName());
		$this->cvDao = $this->em->getDao(Cv::getClassName());
	}

	// <editor-fold defaultstate="colapsed" desc="create & add & edit">
	// </editor-fold>
	// <editor-fold defaultstate="colapsed" desc="getters">
	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="finders">

	public function findCvs(Job $job)
	{
		return $this->cvDao->findBySkillRequests($job->skillRequests);
	}

	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="checkers">
	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="delete">
	// </editor-fold>
}
