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

}
