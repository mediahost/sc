<?php

namespace App\Model\Repository\Finders;

use Kdyby\Doctrine\QueryBuilder;
use Nette\Object;

abstract class Finder extends Object implements IFinder
{

	/** @var QueryBuilder */
	protected $qb;

	public function __construct(QueryBuilder $qb)
	{
		$this->qb = $qb;
		$this->init();
	}

	public function getResult()
	{
		return $this->getQuery()->getResult();
	}
    
    public function getSingleScalarResult() {
        return $this->getQuery()->getSingleScalarResult();
    }

	protected function getQuery()
	{
		$this->build();
		return $this->qb->getQuery();
	}

	protected function getExpr()
	{
		return $this->qb->expr();
	}

	protected function setParameter($key, $value)
	{
		$this->qb->setParameter($key, $value);
	}

	abstract protected function init();

	abstract protected function build();
}
