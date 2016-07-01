<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Model\Facade\UserFacade;
use App\Model\Entity\Cv;
use App\Model\Entity\Job;
use App\Model\Entity\SkillKnowRequest;
use Kdyby\Doctrine\EntityManager;

class CvDataView extends BaseControl
{
    /** @var UserFacade @inject */
	public $userFacade;
    
	/** @var EntityManager @inject */
	public $em;

	/** @var SkillKnowRequest[] */
	private $skillRequests = [];


	public function setJob(Job $job)
	{
		return $this->setSkillRequests($job->getSkillRequests());
	}


	public function setSkillRequests($skillRequests)
	{
		foreach ($skillRequests as $id => $skillRequest) {
			$this->skillRequests[$id] = $skillRequest;
		}
		return $this;
	}

	private function getCvs()
	{
		$cvRepo = $this->em->getRepository(Cv::getClassName());

		if (count($this->skillRequests)) {
			return $cvRepo->findBySkillRequests($this->skillRequests);
		}

		return $cvRepo->findAll();
	}


	public function render()
	{
		$this->template->cvs = $this->getCvs();
        $this->template->addFilter('canAccess', $this->userFacade->canAccess);
		parent::render();
	}
}

Interface ICvDataViewFactory
{
	/** @return CvDataView */
	public function create();
}