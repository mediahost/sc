<?php

namespace App\Model\Repository;

use App\Model\Entity\Job;
use App\Model\Entity\User;
use Exception;
use Kdyby\Doctrine\QueryException;

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

		$qb = $this->createQueryBuilder()
			->select("e.$value", "e.$key")
			->from($this->getEntityName(), 'e', 'e.' . $key)
			->innerJoin('e.roles', 'r')
			->autoJoinOrderBy((array)$orderBy);
		if (is_array($roleId)) {
			$qb->where('r.id IN (:roleId)');
		} else {
			$qb->where('r.id = :roleId');
		}
		$query = $qb
			->setParameter('roleId', $roleId)
			->getQuery();

		try {
			$getFirst = function ($row) {
				return reset($row);
			};
			return array_map($getFirst, $query->getArrayResult());
		} catch (Exception $e) {
			throw new QueryException($e, $query);
		}
	}

	/**
	 * @deprecated Use UserFacade::delete() instead
	 * @param User $entity
	 */
	public function delete($entity)
	{
		$className = UserRepository::getClassName();
		throw new RepositoryException('Use ' . $className . '::delete() instead.');
	}

	public function findAccountManagers(array $criteria = [], array $orderBy = [], $limit = null, $offset = null)
	{
		$key = $this->getClassMetadata()->getSingleIdentifierFieldName();
		$qb = $this->createQueryBuilder('e')
			->whereCriteria($criteria)
			->resetDQLPart('from')->from($this->getEntityName(), 'e', 'e.' . $key)
			->join(Job::getClassName(), 'j', 'WITH', 'e = j.accountManager')
			->autoJoinOrderBy((array)$orderBy);

		return $qb->getQuery()
			->setMaxResults($limit)
			->setFirstResult($offset)
			->getResult();
	}

}
