<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityRepository;

class UserRepository extends EntityRepository
{

	public function findPairsByRoleId($roleId, $value = NULL, $orderBy = array(), $key = NULL)
	{
		if (!is_array($orderBy)) {
			$key = $orderBy;
			$orderBy = [];
		}
		
		if (empty($key)) {
			$key = $this->getClassMetadata()->getSingleIdentifierFieldName();
		}
		
		$qb = $this->getEntityManager()->createQueryBuilder();
		$query = $qb->select("e.$value", "e.$key")
				->from($this->getEntityName(), 'e', 'e.' . $key)
				->innerJoin('e.roles', 'r')
				->where('r.id = :roleid')
				->setParameter('roleid', $roleId)
				->getQuery();

		try {
			return array_map(function ($row) {
				return reset($row);
			}, $query->getArrayResult());

		} catch (\Exception $e) {
			throw $this->handleException($e, $query);
		}
	}

}
