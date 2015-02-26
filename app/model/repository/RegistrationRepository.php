<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityRepository;

class RegistrationRepository extends EntityRepository
{

	public function deleteByMail($mail)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		return $qb->delete($this->getEntityName(), 'r')
						->where('r.mail = ?1')
						->setParameter(1, $mail)
						->getQuery()
						->execute();
	}

}
