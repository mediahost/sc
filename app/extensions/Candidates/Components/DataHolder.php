<?php

namespace App\Extensions\Candidates\Components;

use App\Model\Entity\Candidate;
use App\Model\Entity\Category;
use App\Model\Entity\Job;
use App\Model\Entity\Match;
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

	const ORDER_BY_ID = 'id';

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

	public function setSorting($by, $dir = Criteria::DESC)
	{
		switch ($by) {
			case self::ORDER_BY_ID:
				$by = 'id';
				break;
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
		$this->candidateCriteria[CandidateRepository::CRITERIA_KEY_ACTIVE] = TRUE;
		$this->candidateCriteria[CandidateRepository::CRITERIA_KEY_ROLE] = $roleRepo->findOneByName(Role::CANDIDATE);
		return $this;
	}

	public function filterFulltext($text)
	{
		$words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
		if (count($words)) {
			$this->candidateCriteria[CandidateRepository::CRITERIA_KEY_FULLTEXT] = $words;
		}
		return $this;
	}

	public function filterJob($job, $state = NULL, $onlyNonRejected = NULL)
	{
		$this->candidateCriteria[CandidateRepository::CRITERIA_KEY_JOB] = $job;
		if ($state) {
			$this->candidateCriteria[CandidateRepository::CRITERIA_KEY_MATCH] = $state;
		} else if ($onlyNonRejected) {
			$this->candidateCriteria[CandidateRepository::CRITERIA_KEY_NOT_REJECT] = TRUE;
		}
		return $this;
	}

	public function filterCategories(array $categories)
	{
		$this->candidateCriteria[CandidateRepository::CRITERIA_KEY_CATEGORIES] = $categories;
		return $this;
	}

	public function filterCandidates($value = NULL)
	{
		$this->candidateCriteria[CandidateRepository::CRITERIA_KEY_CANDIDATES] = $value;
		return $this;
	}

	public function filterItSkills(array $skills)
	{
		$this->candidateCriteria[CandidateRepository::CRITERIA_KEY_SKILLS] = $skills;
		return $this;
	}

	public function filterCountry($value = NULL)
	{
		$this->candidateCriteria[CandidateRepository::CRITERIA_KEY_COUNTRY] = $value;
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
