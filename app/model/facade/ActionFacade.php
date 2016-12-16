<?php

namespace App\Model\Facade;

use App\Model\Entity\Action;
use App\Model\Entity\Job;
use App\Model\Entity\User;
use Doctrine\ORM\EntityRepository;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class ActionFacade extends Object
{

	/** @var EntityManager @inject */
	public $em;

	/** @var EntityRepository @inject */
	private $actionRepo;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->actionRepo = $this->em->getRepository(Action::getClassName());
	}

	public function addJobView(User $user, Job $job)
	{
		$action = new Action($user, Action::TYPE_JOB_VIEW);
		$action->job = $job;

		$this->actionRepo->save($action);

		return $action;
	}

	public function addJobApply(User $user, Job $job)
	{
		$action = new Action($user, Action::TYPE_JOB_APPLY);
		$action->job = $job;

		$this->actionRepo->save($action);

		return $action;
	}

}
