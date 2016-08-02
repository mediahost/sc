<?php

namespace App\Model\Repository\Finders\CvRepository;

use App\Model\Repository\Finders\Finder;

class FinderCvsByCandidateRequests extends Finder
{
    /** @var bool */
    private $joinUserTable = FALSE;
    
    /** @var array */
	private $orRequests = [];

	/** @var array */
	private $andRequests;
    
    /** @var array */
    private $categoryRequests = [];
    
    /** @var array */
    private $locationRequests = [];

    /** @var array */
    private $searchRequests = [];



    protected function init()
	{
		$this->qb->innerJoin('e.candidate', 'c');
	}
    
    public function addCategoryRequest($request)
	{
        $key = 'categoryId' . count($this->categoryRequests);
        $this->categoryRequests[] = $this->getExpr()->like('c.jobCategories', ':'.$key);
        $this->setParameter($key, '%:'.$request.';%');
    }
    
    public function addLocationRequest($request) {
        $key = 'location' . count($this->locationRequests);
        $this->locationRequests[] = $this->getExpr()->like('c.workLocations', ':'.$key);
        $this->setParameter($key, '%:'.$request.';%');
    }
    
    public function addSearchRequest($request) {
        $this->joinUserTable = TRUE;
        $this->searchRequests[] = $this->getExpr()->like('c.firstname', ':firstname');
		$this->setParameter('firstname', '%'.$request.'%');
        $this->searchRequests[] = $this->getExpr()->like('c.surname', ':surname');
		$this->setParameter('surname', '%'.$request.'%');
        $this->searchRequests[] = $this->getExpr()->like('u.mail', ':email');
		$this->setParameter('email', $request);
    }
    
    public function build()
	{
        $this->buildJoins();
        $this->buildCategoryRequests();
        $this->buildLocationRequests();
        $this->buildSearchRequests();
	}
    
    private function buildJoins()
	{
		if ($this->joinUserTable) {
            $this->qb->innerJoin('c.user', 'u');
		}
	}
    
    private function buildCategoryRequests() {
        $ors = call_user_func_array([$this->getExpr(), 'orX'], $this->categoryRequests);
        $this->qb->andWhere($ors);
    }
    
    private function buildLocationRequests() {
        $ors = call_user_func_array([$this->getExpr(), 'orX'], $this->locationRequests);
        $this->qb->andWhere($ors);
    }
    
    private function buildSearchRequests() {
        $ors = call_user_func_array([$this->getExpr(), 'orX'], $this->searchRequests);
        $this->qb->andWhere($ors);
    }
}
