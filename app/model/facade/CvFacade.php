<?php

namespace App\Model\Facade;

use App\Model\Entity\Candidate;
use App\Model\Entity\Cv;
use App\Model\Entity\EntityException;
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
    
    public function find($id) {
        return $this->cvRepo->find($id);
    }

    public function getDefaultCvOrCreate(Candidate $candidate)
	{
		try {
			$defaultCv = $candidate->defaultCv;
		} catch (EntityException $e) {
			$defaultCv = $this->create($candidate);
			$this->setAsDefault($defaultCv);
		}
		return $defaultCv;
	}

	public function create(Candidate $candidate, $name = NULL)
	{
		$cv = new Cv($name);
		$cv->candidate = $candidate;
		$cv->isDefault = !$cv->candidate->hasDefaultCv();
		$this->cvRepo->save($cv);
		return $cv;
	}

	/**
	 * Set Cv as default and reset other default CV
	 * @param Cv $cv
	 * @return self
	 */
	public function setAsDefault(Cv $cv)
	{
		if (!$cv->isDefault) {
			return $this;
		}
		try {
			$defaultCv = $cv->candidate->getDefaultCv();
			if ($defaultCv->id !== $cv->id) {
				$this->switchDefaults($cv, $defaultCv);
			}
		} catch (EntityException $e) {
			$cv->isDefault = TRUE;
			$this->cvRepo->save($cv);
		}
		return $this;
	}

	public function findJobs(Cv $cv)
	{
		return $this->jobRepo->findBySkillKnows($cv->skillKnows);
	}

	private function switchDefaults(Cv $toOn, Cv $toOff)
	{
		$toOff->isDefault = FALSE;
		$toOn->isDefault = TRUE;
		$this->em->persist($toOn);
		$this->em->persist($toOff);
		$this->em->flush();
		return $this;
	}

}
