<?php

namespace App\Model\Repository;

use App\Model\Repository\Finders\JobRepository\FinderJobsBySkillKnows;

class JobRepository extends BaseRepository
{

	public function findBySkillKnows(array $skillKnows)
	{
		$qb = $this->createQueryBuilder('e');
		$finder = new FinderJobsBySkillKnows($qb);
		foreach ($skillKnows as $skillKnow) {
			$finder->addKnow($skillKnow);
		}
		return $finder->getResult();
	}

}
