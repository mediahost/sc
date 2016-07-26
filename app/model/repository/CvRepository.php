<?php

namespace App\Model\Repository;

use App\Model\Repository\Finders\CvRepository\FinderCvsBySkillRequests;
use App\Model\Repository\Finders\CvRepository\FinderCvsBySearch;

class CvRepository extends BaseRepository
{

	public function findByRequests($requests=null, $first=1, $count=18)
	{
		$qb = $this->createQueryBuilder('e');
        $qb->setFirstResult($first);
        $qb->setMaxResults($count);
        
        if($requests['search']) {
            $finder = new FinderCvsBySearch($qb);
            $finder->addRequest($requests['search']);
            return $finder->getResult();
        }
        
        if($requests['skill']) {
            $finder = new FinderCvsBySkillRequests($qb);
            foreach ($requests['skill'] as $skillRequest) {
                $finder->addRequest($skillRequest);
            }
            return $finder->getResult();
        }
		return $qb->getQuery()->getResult();
	}
    
    public function countOfCvs($skillRequests=null) {
        $qb = $this->createQueryBuilder('e');
        $qb->select($qb->expr()->count('e.id'));
        if($skillRequests) {
            $finder = new FinderCvsBySkillRequests($qb);
            foreach ($skillRequests as $skillRequest) {
                $finder->addRequest($skillRequest);
            }
            return $finder->getSingleScalarResult();
        }
        return $qb->getQuery()->getSingleScalarResult();
    }
}
