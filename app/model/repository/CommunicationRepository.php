<?php

namespace App\Model\Repository;

use App\Model\Entity\Candidate;
use App\Model\Entity\Job;
use App\Model\Entity\Sender;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;

class CommunicationRepository extends BaseRepository
{

	public function findOneByContributors(array $contributors, $subject, Job $job = NULL, Candidate $candidate = NULL)
	{
		$qb = $this->createQueryBuilder('c')
			->where('c.contributorsCount = :contributorsCount')
			->setParameter('contributorsCount', count($contributors));

		$andx = new Andx();
		foreach ($contributors as $key => $contributor) {
			$orx = new Orx();
			$orx->add('c.contributorsArray LIKE :contributorA_' . $key);
			$orx->add('c.contributorsArray LIKE :contributorB_' . $key);
			$orx->add('c.contributorsArray LIKE :contributorC_' . $key);
			$orx->add('c.contributorsArray LIKE :contributorD_' . $key);
			$qb->setParameter('contributorA_' . $key, '%,' . $contributor->id . ',%');
			$qb->setParameter('contributorB_' . $key, '%,' . $contributor->id . '');
			$qb->setParameter('contributorC_' . $key, '' . $contributor->id . ',%');
			$qb->setParameter('contributorD_' . $key, '' . $contributor->id . '');
			$andx->add($orx);
		}
		$qb->andWhere($andx);

		if ($subject) {
			$qb->andWhere('c.subject = :subject')
				->setParameter('subject', $subject);
		}
		if ($job) {
			$qb->andWhere('c.job = :job')
				->setParameter('job', $job);
		}
		if ($candidate) {
			$qb->andWhere('c.candidate = :candidate')
				->setParameter('candidate', $candidate);
		}

		return $qb->setMaxResults(1)
			->getQuery()->getOneOrNullResult();
	}

	public function findByContributors(array $contributors)
	{
		$qb = $this->createQueryBuilder('c')
			->where('c.contributorsCount = :contributorsCount')
			->setParameter('contributorsCount', count($contributors));

		$andx = new Andx();
		foreach ($contributors as $key => $contributor) {
			$orx = new Orx();
			$orx->add('c.contributorsArray LIKE :contributorA_' . $key);
			$orx->add('c.contributorsArray LIKE :contributorB_' . $key);
			$orx->add('c.contributorsArray LIKE :contributorC_' . $key);
			$orx->add('c.contributorsArray LIKE :contributorD_' . $key);
			$qb->setParameter('contributorA_' . $key, '%,' . $contributor->id . ',%');
			$qb->setParameter('contributorB_' . $key, '%,' . $contributor->id . '');
			$qb->setParameter('contributorC_' . $key, '' . $contributor->id . ',%');
			$qb->setParameter('contributorD_' . $key, '' . $contributor->id . '');
			$andx->add($orx);
		}
		$qb->andWhere($andx);

		return $qb->getQuery()->getResult();
	}

	public function findByFulltext(Sender $me, $text)
	{
		$criteria = [
			'contributors.id' => $me,
			'subject LIKE' => '%' . $text . '%',
			'messages.text LIKE' => '%' . $text . '%',
		];
		return $this->findBy($criteria);
	}

}
