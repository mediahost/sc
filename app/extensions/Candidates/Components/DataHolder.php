<?php

namespace App\Extensions\Candidates\Components;

use App\Model\Entity\Candidate;
use App\Model\Entity\Category;
use App\Model\Entity\Job;
use App\Model\Entity\Parameter;
use App\Model\Entity\Price;
use App\Model\Entity\Producer;
use App\Model\Entity\ProducerLine;
use App\Model\Entity\ProducerModel;
use App\Model\Entity\Product;
use App\Model\Entity\Role;
use App\Model\Entity\Stock;
use App\Model\Entity\Vat;
use App\Model\Repository\CandidateRepository;
use App\Model\Repository\CategoryRepository;
use App\Model\Repository\ProductRepository;
use App\Model\Repository\StockRepository;
use Doctrine\Common\Collections\Criteria;
use Exception;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

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
				$this->candidates = $this->candidateRepo->findByFilter($this->candidateCriteria, $this->orderBy, $this->limit, $this->offset);
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
				$this->count = $this->candidateRepo->countByFilter($this->candidateCriteria);
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
		$roleRepo = $this->em->getRepository(Role::getClassName());
		$this->candidateCriteria['active'] = TRUE;
		$this->candidateCriteria['role'] = $roleRepo->findOneByName(Role::CANDIDATE);
		return $this;
	}

	public function filterFulltext($text)
	{
		$words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
		if (count($words)) {
			$this->candidateCriteria['fulltext'] = $words;
		}
		return $this;
	}

	public function filterJob(Job $job)
	{
		$this->candidateCriteria['job'] = $job;
		return $this;
	}

	public function filterJobs(array $jobs)
	{
		$this->candidateCriteria['jobs'] = $jobs;
		return $this;
	}

	public function filterCategories(array $categories)
	{
		$this->candidateCriteria['categories'] = $categories;
		return $this;
	}

	public function filterItSkills(array $skills)
	{
		$this->candidateCriteria['skills'] = $skills;
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
