<?php

namespace App\Extensions\Candidates\Components;

use App\Model\Entity\Candidate;
use App\Model\Entity\Category;
use App\Model\Entity\Parameter;
use App\Model\Entity\Price;
use App\Model\Entity\Producer;
use App\Model\Entity\ProducerLine;
use App\Model\Entity\ProducerModel;
use App\Model\Entity\Product;
use App\Model\Entity\Stock;
use App\Model\Entity\Vat;
use App\Model\Repository\BaseRepository;
use App\Model\Repository\CandidateRepository;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ProductRepository;
use App\Model\Repository\StockRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
use Exception;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;
use Nette\Utils\DateTime;
use Tracy\Debugger;

class DataHolder extends Object
{

	const ORDER_BY_NAME = 'name';

	/** @var EntityManager @inject */
	public $em;

	/** @var CandidateRepository */
	private $candidateRepo;

	/** @var array */
	private $candidates;

	/** @var int total count of items */
	private $count;

	/** @var int */
	private $limit;

	/** @var int */
	private $offset;

	/** @var array */
	private $candidateCriteria = [];

	/** @var array */
	private $orderBy = [];

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->candidateRepo = $this->em->getRepository(Candidate::getClassName());
	}


	// <editor-fold defaultstate="collapsed" desc="public setters">

	public function setPaging($limit = NULL, $offset = NULL)
	{
		$this->limit = $limit;
		$this->offset = $offset;
	}

	public function setSorting($by, $dir = Criteria::ASC)
	{
		switch ($by) {
//			case self::ORDER_BY_NAME:
//				$by = 'name';
//				break;
			default:
				return $this;
		}
		$dir = $dir === Criteria::DESC ? $dir : Criteria::ASC;
		$this->orderBy = [$by => $dir];
		return $this;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="public getters">

	public function getCandidates()
	{
		if (!$this->candidates) {
			try {
				$this->candidates = $this->candidateRepo->findBy($this->candidateCriteria, $this->orderBy, $this->limit, $this->offset);
			} catch (DataHolderException $e) {
				$this->candidates = [];
			}
		}
		return $this->candidates;
	}

	public function getCount()
	{
		if ($this->count === NULL) {
			try {
				$this->count = $this->candidateRepo->countBy($this->candidateCriteria);
			} catch (DataHolderException $e) {
				$this->count = 0;
			}
		}
		return $this->count;
	}

	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="add filters">

	public function filterNotEmpty()
	{
		$this->candidateCriteria['person.photo NOT'] = NULL;
		$this->candidateCriteria['person.firstname NOT'] = NULL;
		return $this;
	}

	public function filterFulltext($text)
	{
		$words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
		return $this;
	}

	// </editor-fold>

}

class DataHolderException extends Exception
{

}

interface IDataHolderFactory
{

}