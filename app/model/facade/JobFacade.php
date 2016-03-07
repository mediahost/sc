<?php

namespace App\Model\Facade;

use App\Model\Entity\Cv;
use App\Model\Entity\Job;
use App\Model\Repository\CvRepository;
use Kdyby\Doctrine\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Object;

class JobFacade extends Object
{

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

	/**
	 * @return ArrayCollection
	 */
	public function findAll() 
	{
		$jobs = $this->jobDao->findAll();
		return new ArrayCollection($jobs);
	}
	
	/**
	 * @param \App\Model\Entity\Company $company
	 * @return ArrayCollection
	 */
	public function findByCompany(\App\Model\Entity\Company $company) 
	{
		$jobs = $this->jobDao->findBy(['company.id' => $company->id]);
		return new ArrayCollection($jobs);
	}
	
	/**
	 * @param \Nette\Security\User $user
	 * @return ArrayCollection
	 */
	public function findByUser(\Nette\Security\User $user)
	{
		$jobs = new ArrayCollection;
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
		$typeRepo = $this->em->getDao(\App\Model\Entity\JobType::getClassName());
		return $typeRepo->find($idType);
	}
	
	public function findJobCategory($idCategory)
	{
		$categoryRepo = $this->em->getDao(\App\Model\Entity\JobCategory::getClassName());
		return $categoryRepo->find($idCategory);
	}
	
	public function getJobTypes()
	{
		$typeRepo = $this->em->getDao(\App\Model\Entity\JobType::getClassName());
		return $typeRepo->findPairs('name');
	}
	
	public function getJobCategories()
	{
		$categoryRepo = $this->em->getDao(\App\Model\Entity\JobCategory::getClassName());
		return $categoryRepo->findPairs('name');
	}
	
	public function delete($id)
	{
		$job = $this->jobDao->find($id);
		$this->jobDao->delete($job);
	}
}
