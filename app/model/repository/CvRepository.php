<?php

namespace App\Model\Repository;

use App\Model\Repository\Finders\CvRepository\FinderCvsBySkillRequests;

class CvRepository extends BaseRepository
{

	public function findBySkillRequests($skillRequests=null, $first=1, $count=18)
	{
		$qb = $this->createQueryBuilder('e');
        $qb->setFirstResult($first);
        $qb->setMaxResults($count);
        if($skillRequests) {
            $finder = new FinderCvsBySkillRequests($qb);
            foreach ($skillRequests as $skillRequest) {
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
