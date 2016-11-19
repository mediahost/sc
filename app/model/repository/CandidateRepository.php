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

	const CRITERIA_KEY_ACTIVE = 'active';
	const CRITERIA_KEY_ROLE = 'role';
	const CRITERIA_KEY_FULLTEXT = 'fulltext';
	const CRITERIA_KEY_JOB = 'job';
	const CRITERIA_KEY_MATCH = 'match';
	const CRITERIA_KEY_NOT_REJECT = 'notReject';
	const CRITERIA_KEY_CATEGORIES = 'categories';
	const CRITERIA_KEY_SKILLS = 'skills';

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
				case self::CRITERIA_KEY_ACTIVE:
					$joins['c.person'] = 'p';
					$joins['p.user'] = 'u';
					$condition->add('u.verificated = :verificated');
					$params[':verificated'] = (bool)$value;
					break;
				case self::CRITERIA_KEY_ROLE:
					if ($value instanceof Role) {
						$joins['c.person'] = 'p';
						$joins['p.user'] = 'u';
						$joins['u.roles'] = 'r';
						$condition->add('r = :role');
						$params[':role'] = $value;
					}
					break;
				case self::CRITERIA_KEY_FULLTEXT:
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
				case self::CRITERIA_KEY_JOB:
					if ($value instanceof Job) {
						$joins[Match::getClassName()] = ['m', 'WITH', 'c = m.candidate'];
						$condition->add('m.job = :job');
						$params[':job'] = $value->id;
					} else if (is_array($value) && count($value)) {
						$joins[Match::getClassName()] = ['m', 'WITH', 'c = m.candidate'];
						$partOr = new Orx();
						foreach ($value as $i => $val) {
							if ($val instanceof Job) {
								$partOr->add('m.job = :job' . $i);
								$params[':job' . $i] = $val->id;
							}
						}
						$condition->add($partOr);
					}
					break;
				case self::CRITERIA_KEY_MATCH:
					$joins[Match::getClassName()] = ['m', 'WITH', 'c = m.candidate'];
					$match = 'm.candidateApprove = TRUE AND m.adminApprove = TRUE';
					$matchOnly = $match . ' AND m.accept IS NULL';
					$accepted = $match . ' AND m.accept = TRUE';
					$rejected = $match . ' AND m.accept = FALSE';
					$acceptedOnly = $accepted . ' AND m.state IS NULL';
					switch ($value) {
						case Match::STATE_APPROVED:
						case Match::STATE_MATCHED:
							$condition->add($match);
							break;
						case Match::STATE_MATCHED_ONLY:
							$condition->add($matchOnly);
							break;
						case Match::STATE_REJECTED:
							$condition->add($rejected);
							break;
						case Match::STATE_ACCEPTED:
							$condition->add($accepted);
							break;
						case Match::STATE_ACCEPTED_ONLY:
							$condition->add($acceptedOnly);
							break;
						default:
							$condition->add($accepted . ' AND m.state = :state');
							$params[':state'] = $value;
							break;
					}
					break;
				case self::CRITERIA_KEY_NOT_REJECT:
					$joins[Match::getClassName()] = ['m', 'WITH', 'c = m.candidate'];
					$condition->add('m.accept != FALSE OR m.accept IS NULL');
					break;
				case self::CRITERIA_KEY_CATEGORIES:
					if (is_array($value) && count($value)) {
						foreach ($value as $key => $item) {
							$condition->add(':jobCategory' . $key . ' MEMBER OF c.jobCategories');
							$params[':jobCategory' . $key] = $item;
						}
					}
					break;
				case self::CRITERIA_KEY_SKILLS:
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
