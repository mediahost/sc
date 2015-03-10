<?php

namespace App\Model\Repository;

use Exception;

class UserRepository extends BaseRepository
{

	public function findPairsByRoleId($roleId, $value = NULL, $orderBy = [], $key = NULL)
	{
		if (!is_array($orderBy)) {
			$key = $orderBy;
			$orderBy = [];
		}

		if (empty($key)) {
			$key = $this->getClassMetadata()->getSingleIdentifierFieldName();
		}

		$query = $this->createQueryBuilder()
				->select("e.$value", "e.$key")
				->from($this->getEntityName(), 'e', 'e.' . $key)
				->innerJoin('e.roles', 'r')
				->where('r.id = :roleid')
				->setParameter('roleid', $roleId)
				->autoJoinOrderBy((array) $orderBy)
				->getQuery();

		try {
			$getFirst = function ($row) {
				return reset($row);
			};
			return array_map($getFirst, $query->getArrayResult());
		} catch (Exception $e) {
			throw $this->handleException($e, $query);
		}
	}

}
