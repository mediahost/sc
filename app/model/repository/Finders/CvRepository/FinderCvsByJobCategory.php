<?php

namespace App\Model\Repository\Finders\CvRepository;

use App\Model\Repository\Finders\Finder;

class FinderCvsByJobCategory extends Finder
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
    
    public function addRequest($requests)
	{
        foreach ($requests as $categoryId=>$category) {
            $this->orRequests[] = $this->getExpr()->like('c.jobCategories', ':categoryId');
            $this->setParameter('categoryId', '%'.$categoryId.'%');
        }
    }
    
    protected function build()
	{
		$this->buildOrs();
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
