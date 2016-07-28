<?php

namespace App\Model\Repository;

use App\Model\Repository\Finders\CvRepository\FinderCvsBySkillRequests;
use App\Model\Repository\Finders\CvRepository\FinderCvsByCandidateRequests;
use App\Model\Repository\Finders\CvRepository\FinderCvsByJobCategory;
use App\Model\Repository\Finders\CvRepository\FinderCvsByLocation;


class CvRepository extends BaseRepository
{

	public function findByRequests($requests=null, $first=1, $count=18)
	{
		$qb = $this->createQueryBuilder('e');
        $qb->setFirstResult($first);
        $qb->setMaxResults($count);
        
        if($requests['search']) {
            $finderByCandidate = new FinderCvsByCandidateRequests($qb);
            $finderByCandidate->addSearchRequest($requests['search']);
        }
        
        if($requests['category']) {
            if (!isset($finderByCandidate)) {
                $finderByCandidate = new FinderCvsByCandidateRequests($qb);
            }
            foreach ($requests['category'] as $categoryId=>$category) {
                $finderByCandidate->addCategoryRequest($categoryId);
            }
        }
        
        if($requests['location']) {
            if (!isset($finderByCandidate)) {
                $finderByCandidate = new FinderCvsByCandidateRequests($qb);
            }
            foreach ($requests['location'] as $locationId=>$location) {
                $finderByCandidate->addLocationRequest($locationId);
            }
        }
        
        if (isset($finderByCandidate)) {
            $finderByCandidate->build();
        }
        
        if($requests['skill']) {
            $finder = new FinderCvsBySkillRequests($qb);
            foreach ($requests['skill'] as $skillRequest) {
                $finder->addRequest($skillRequest);
            }
            $finder->build();
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
