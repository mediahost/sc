<?php

namespace App\Model\Service;

use App\Model\Entity\Sender;
use App\Model\Entity\User;
use Doctrine\ORM\EntityManager;
use Nette\Object;

class CandidateCleaner extends Object
{
	/** @var EntityManager @inject */
	public $em;

	public function removeGeneratedCandidates()
	{
		$useRepo = $this->em->getRepository(User::getClassName());
		$users = $useRepo->createQueryBuilder('u')
			->where("u.mail LIKE '%example.dev'")
			->getQuery()->getResult();

		$senders = $this->em->getRepository(Sender::getClassName())->createQueryBuilder('s')
			->leftJoin('s.user', "u")
			->where('u IN(:user)')
			->setParameter('user', $users)
			->getQuery()->getResult();

		foreach ($senders as $sender) {
			$this->em->remove($sender);
		}
		foreach ($users as $user) {
			$this->em->remove($user);
		}
		$this->em->flush();
	}
}