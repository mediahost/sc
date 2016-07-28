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
    
    
    protected function init()
	{
		$this->qb->innerJoin('e.candidate', 'c');
	}
    
    public function addCategoryRequest($request)
	{
        $this->orRequests[] = $this->getExpr()->like('c.jobCategories', ':categoryId');
        $this->setParameter('categoryId', '%:'.$request.';%');
    }
    
    public function addLocationRequest($request) {
        $this->orRequests[] = $this->getExpr()->like('c.workLocations', ':location');
        $this->setParameter('location', '%:'.$request.';%');
    }
    
    public function addSearchRequest($request) {
        $this->joinUserTable = TRUE;
        $this->orRequests[] = $this->getExpr()->like('c.firstname', ':firstname');
		$this->setParameter('firstname', '%'.$request.'%');
        $this->orRequests[] = $this->getExpr()->like('c.surname', ':surname');
		$this->setParameter('surname', '%'.$request.'%');
        $this->orRequests[] = $this->getExpr()->like('u.mail', ':email');
		$this->setParameter('email', $request);
    }
    
    public function build()
	{
		$this->buildOrs();
        $this->buildJoins();
	}
    
    private function buildJoins()
	{
		if ($this->joinUserTable) {
            $this->qb->innerJoin('c.user', 'u');
		}
	}
    
    private function buildAnds()
	{
		$this->orRequests[] = call_user_func_array([$this->getExpr(), 'andX'], $this->andRequests);
	}

	private function buildOrs()
	{
		$ors = call_user_func_array([$this->getExpr(), 'orX'], $this->orRequests);
		$this->qb->andWhere($ors);
	}
}
