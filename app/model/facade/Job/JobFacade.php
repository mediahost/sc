<?php

namespace App\Model\Facade;


use App\Model\Entity\JobCategory;
use App\Model\Entity\JobType;
use App\Model\Facade\Traits\JobCategoryFacade;
use App\Model\Facade\Traits\JobMatchingFacade;
use App\Model\Entity\Cv;
use App\Model\Entity\Job;
use App\Model\Repository\CvRepository;
use App\Model\Repository\JobRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class JobFacade extends Object
{
    use JobCategoryFacade;
    use JobMatchingFacade;

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

	public function findCvs(Job $job)
	{
		return $this->cvRepo->findBySkillRequests($job->skillRequests->toArray());
	}
    
    public function find($id) {
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
		if(!isset($entity)) {
			$entity = new JobType();
			$entity->name = $type;
			$typeRepo->save($entity);
		}
		return $entity;
	}
	
	public function getJobTypes()
	{
		$typeRepo = $this->em->getDao(JobType::getClassName());
		return $typeRepo->findPairs('name');
	}
	
	public function getJobCategories()
	{
		$categoryRepo = $this->em->getDao(JobCategory::getClassName());
		return $categoryRepo->findPairs('name');
	}
	
	public function delete($id)
	{
		$job = $this->jobRepo->find($id);
		$this->jobRepo->delete($job);
	}
}
