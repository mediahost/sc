<?php

namespace App\Model\Facade;

use App\Components\User\User;
use App\Model\Entity\Company;
use App\Model\Entity\JobCategory;
use App\Model\Entity\JobType;
use App\Model\Facade\Traits\JobCategoryFacade;
use App\Model\Facade\Traits\JobMatchingFacade;
use App\Model\Entity\Cv;
use App\Model\Entity\Job;
use App\Model\Repository\CvRepository;
use Kdyby\Doctrine\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Object;

class JobFacade extends Object
{
    use JobCategoryFacade;
    use JobMatchingFacade;

	/** @var EntityManager @inject */
	public $em;

	/** @var CvRepository */
	private $cvDao;
	
	/** @var JobRepository */
	private $jobDao;

	
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->cvDao = $this->em->getDao(Cv::getClassName());
		$this->jobDao = $this->em->getDao(Job::getClassName());
	}

	public function findCvs(Job $job)
	{
		return $this->cvDao->findBySkillRequests($job->skillRequests->toArray());
	}

	/** TODO: Refactoring */
	public function findAll() 
	{
		$jobs = $this->jobDao->findAll();
		return new ArrayCollection($jobs);
	}
    
    public function find($id) {
        return $this->jobDao->find($id);
    }

	/** TODO: Refactoring - Proč vrací arrayCollection? */
	public function findByCompany(Company $company)
	{
		$jobs = $this->jobDao->findBy(['company.id' => $company->id]);
		return new ArrayCollection($jobs);
	}

	/** TODO: Refactoring */
	public function findByUser(User $user)
	{
		$jobs = new ArrayCollection();
		$alowedCompanies = new ArrayCollection($user->identity->allowedCompanies);
		foreach ($alowedCompanies as $permission) {
			foreach ($permission->company->getJobs() as $job) {
				$jobs->add($job);
			}
		}
		return $jobs;
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
		$job = $this->jobDao->find($id);
		$this->jobDao->delete($job);
	}
}
