<?php

namespace App\Components\Cv;

use App\Components\BaseControl;
use App\Model\Entity\Cv;
use App\Model\Entity\Job;
use App\Model\Entity\SkillKnowRequest;
use Kdyby\Doctrine\EntityManager;

class CvDataView extends BaseControl
{

	/** @var EntityManager @inject */
	public $em;

	/** @var SkillKnowRequest[] */
	private $skillRequests = [];


	public function setJob(Job $job)
	{
		$this->skillRequests = $job->getSkillRequests();
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
		parent::render();
	}
}

Interface ICvDataViewFactory
{
	/** @return CvDataView */
	public function create();
}