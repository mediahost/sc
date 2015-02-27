<?php

namespace App\Model\Repository;

use App\Model\Repository\Finders\CvRepository\FinderCvsBySkillRequests;

class CvRepository extends BaseRepository
{

	public function findBySkillRequests(array $skillRequests)
	{
		$qb = $this->createQueryBuilder('e');
		$finder = new FinderCvsBySkillRequests($qb);
		foreach ($skillRequests as $skillRequest) {
			$finder->addRequest($skillRequest);
		}
		return $finder->getResult();
	}

}
