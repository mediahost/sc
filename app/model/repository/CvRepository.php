<?php

namespace App\Model\Repository;

use App\Model\Repository\Finders\CvRepository\FinderCvsBySkillRequests;
use Kdyby\Doctrine\EntityRepository;

class CvRepository extends EntityRepository
{

	public function findBySkillRequests(array $skillRequests)
	{
		$qb = $this->createQueryBuilder('e');
		$finder = new FinderCvsBySkillRequests($qb);
		foreach ($skillRequests as $skillRequest) {
			$finder->addRequest($skillRequest);
		}
		$query = $finder->getQuery();

		return $query->getResult();
	}

}
