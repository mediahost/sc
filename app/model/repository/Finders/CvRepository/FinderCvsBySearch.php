<?php

namespace App\Model\Repository\Finders\CvRepository;

use App\Model\Repository\Finders\Finder;


class FinderCvsBySearch extends Finder 
{
    /** @var array */
	private $orRequests = [];

	/** @var array */
	private $andRequests;
    
    
    protected function init()
	{
		$this->qb
				->innerJoin('e.candidate', 'c');
	}
    
    public function addRequest($request)
	{
        $this->orRequests[] = $this->getExpr()->like('c.firstname', ':firstname');
		$this->setParameter('firstname', $request.'%');
        $this->orRequests[] = $this->getExpr()->like('c.surname', ':surname');
		$this->setParameter('surname', $request.'%');
        $this->orRequests[] = $this->getExpr()->like('u.mail', ':email');
		$this->setParameter('email', $request);
    }
    
    protected function build()
	{
		$this->buildJoins();
		$this->buildOrs();
	}
    
    private function buildJoins()
	{
        $this->qb->innerJoin('c.user', 'u');
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
