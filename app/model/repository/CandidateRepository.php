<?php

namespace App\Model\Repository;

use App\Model\Entity\Job;
use App\Model\Entity\JobCategory;
use App\Model\Entity\Match;
use App\Model\Entity\Role;
use App\Model\Entity\Skill;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;

class CandidateRepository extends BaseRepository
{

	public function findByFilter(array $criteria, array $orderBy = null, $limit = null, $offset = null)
	{
		$qb = $this->createFiltredQueryBuilder($criteria);
		if ($orderBy) {
			$qb->orderBy((array)$orderBy);
		}

		return $qb->getQuery()
			->setMaxResults($limit)
			->setFirstResult($offset)
			->getResult();
	}

	public function countByFilter(array $criteria = array())
	{
		return $query = $this->createFiltredQueryBuilder($criteria)
			->select('COUNT(c)')
			->setMaxResults(1)
			->getQuery()
			->getSingleScalarResult();
	}

	private function createFiltredQueryBuilder(array $criteria)
	{
		$qb = $this->createQueryBuilder('c');

		$joins = [];
		$params = [];
		$conditions = new Andx();
		foreach ($criteria as $key => $value) {
			$condition = new Andx();
			switch ($key) {
				case 'active':
					$joins['c.person'] = 'p';
					$joins['p.user'] = 'u';
					$condition->add('u.verificated = :verificated');
					$params[':verificated'] = (bool)$value;
					break;
				case 'role':
					if ($value instanceof Role) {
						$joins['c.person'] = 'p';
						$joins['p.user'] = 'u';
						$joins['u.roles'] = 'r';
						$condition->add('r = :role');
						$params[':role'] = $value;
					}
					break;
				case 'fulltext':
					$joins['c.person'] = 'p';
					$joins['p.user'] = 'u';

					foreach ($value as $i => $word) {
						$partOr = new Orx();
						$rules = [
							'p.firstname LIKE',
							'p.surname LIKE',
							'u.mail LIKE',
						];
						foreach ($rules as $rule) {
							$partOr->add($rule . ' :word' . $i);
						}
						$condition->add($partOr);
						$params[':word' . $i] = '%' . $word . '%';
					}
					break;
				case 'job':
					if ($value instanceof Job) {
						$joins[Match::getClassName()] = ['m', 'WITH', 'c = m.candidate AND m.adminApprove = TRUE'];
						$condition->add('m.job = :job');
						$params[':job'] = $value->id;
					}
					break;
				case 'categories':
					if (is_array($value) && count($value)) {
						foreach ($value as $key => $item) {
							$condition->add(':jobCategory' . $key . ' MEMBER OF c.jobCategories');
							$params[':jobCategory' . $key] = $item;
						}
					}
					break;
				case 'skills':
					if (is_array($value) &&
						isset($value['skillRange']) &&
						is_array($value['skillRange']) &&
						count($value['skillRange'])
					) {
						$joins['c.cv'] = 'cv';
						$joins['cv.skillKnows'] = 'sk';

						$partOr = new Orx();
						foreach ($value['skillRange'] as $id => $levels) {
							$partAnd = new Andx();
							$partAnd->add('sk.skill = :skill');
							$partAnd->add('sk.level >= :levelFrom AND sk.level <= :levelTo');

							$params[':skill'] = $this->_em->getRepository(Skill::getClassName())->find($id);
							$params[':levelFrom'] = $levels[0];
							$params[':levelTo'] = $levels[1];

							if (isset($value['yearRange']) && isset($value['yearRange'][$id])) {
								$condition->add('sk.years >= :yearsFrom AND sk.years <= :yearsTo');
								$years = $value['yearRange'][$id];
								$params[':yearsFrom'] = $years[0];
								$params[':yearsTo'] = $years[1];
							}

							$partOr->add($partAnd);
						}
						$condition->add($partOr);
					}
					break;
			}
			$conditions->add($condition);
		}
		if ($conditions->count()) {
			$qb->andWhere($conditions);
		}
		$qb->setParameters($params);

		foreach ($joins as $join => $alias) {
			if (!is_array($alias)) {
				$alias = [$alias];
			}
			call_user_func_array([$qb, 'join'], array_merge([$join], $alias));
		}

		return $qb;
	}

}
