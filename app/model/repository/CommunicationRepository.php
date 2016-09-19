<?php

namespace App\Model\Repository;

use App\Model\Entity\Sender;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;

class CommunicationRepository extends BaseRepository
{

	public function findBySenders(Sender $one, Sender $two)
	{
		$rsm = new ResultSetMapping();
		$rsm->addScalarResult('communication_id', 'id');
		$nqb = $this->createNativeQuery('SELECT t1.communication_id FROM
										(SELECT * FROM `communication_sender` WHERE sender_id = :one) t1
										INNER JOIN
										(SELECT * FROM `communication_sender` WHERE sender_id = :two) t2
										ON t1.communication_id = t2.communication_id', $rsm);
		$nqb->setParameter('one', $one->id);
		$nqb->setParameter('two', $two->id);
		try {
			$id = $nqb->getSingleScalarResult();
			return $this->find($id);
		} catch (NoResultException $e) {
			return NULL;
		}
	}

	public function findByFulltext(Sender $me, $text)
	{
		$criteria = [
			'contributors.id' => $me,
			'messages.text LIKE' => '%' . $text . '%',
		];
		return $this->findBy($criteria);
	}

}
