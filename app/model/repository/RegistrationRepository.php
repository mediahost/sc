<?php

namespace App\Model\Repository;

class RegistrationRepository extends BaseRepository
{

	public function deleteByMail($mail)
	{
		return $this->createQueryBuilder()
						->delete($this->getEntityName(), 'r')
						->where('r.mail = ?1')
						->setParameter(1, $mail)
						->getQuery()
						->execute();
	}

}
