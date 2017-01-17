<?php

namespace App\Model\Repository;

use App\Model\Repository\Finders\JobRepository\FinderJobsBySkillKnows;
use Traversable;

class JobRepository extends BaseRepository
{

	public function findBySkillKnows(Traversable $skillKnows)
	{
		$qb = $this->createQueryBuilder('e');
		$finder = new FinderJobsBySkillKnows($qb);
		foreach ($skillKnows as $skillKnow) {
			$finder->addKnow($skillKnow);
		}
		return $finder->getResult();
	}

	/**
	 * @deprecated Use JobFacade::delete() instead
	 * @param User $entity
	 */
	public function delete($entity)
	{
		$className = 'App\Model\Facade\JobFacade';
		throw new RepositoryException('Use ' . $className . '::delete() instead.');
	}

}
