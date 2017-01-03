<?php

namespace App\Model\Repository;

use App\Model\Entity\Candidate;
use App\Model\Entity\Job;
use App\Model\Entity\Sender;

class CommunicationRepository extends BaseRepository
{

	public function findOneByContributors(array $contributors, $subject, Job $job = NULL, Candidate $candidate = NULL)
	{
		$qb = $this->createQueryBuilder('c')
			->innerJoin('c.contributors', 's')
			->where('s IN (:contributors)')
			->setParameter('contributors', $contributors);
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
