<?php

namespace App\Components\Candidate;

use App\ArrayUtils;
use App\Components\BaseControl;
use App\Helpers;
use Doctrine\ORM\EntityManager;
use App\Model\Entity\Cv;
use App\Components\Candidate\ICandidatePreviewFactory;
use App\Components\Candidate\IMatchingFactory;
use App\Components\Candidate\ILocationFilterFactory;
use App\Components\Candidate\ISearchFilterFactory;
use App\Components\Job\IJobCategoryFilterFactory;
use App\Components\Cv\ISkillsFilterFactory;
use Nette\Application\UI\Multiplier;
use Nette\Http\Session;

class CandidateGalleryView extends BaseControl
{

	static $pagination = [6, 12, 18];

	/** @var IMatchingFactory @inject */
	public $matchingFactory;

	/** @var ISkillsFilterFactory @inject */
	public $skillsFilterFactory;

	/** @var IJobCategoryFilterFactory @inject */
	public $jobCategoryFilterFactory;

	/** @var ILocationFilterFactory @inject */
	public $locationFilterFactory;

	/** @var ISearchFilterFactory @inject */
	public $searchFilterFactory;

	/** @var ICandidatePreviewFactory @inject */
	public $candidatePreviewFactory;

	/** @var EntityManager @inject */
	public $em;

	/** @var Session */
	private $session;

	/** @var Cv[] */
	private $cvs = [];

	/** @var SkillRequest[] */
	private $skillRequests = [];

	/** @var int */
	private $countPerPage;

	/** @var int */
	private $current = 1;


	public function __construct(Session $session)
	{
		parent::__construct();
		$this->countPerPage = self::$pagination[2];
		$this->session = $session->getSection('candidateGalleryView');
	}

	public function render()
	{
		$this->setTemplateFile('candidateGalleryView');
		$this->skillRequests = $this['skillsFilter']->setSkillRequests($this->getSerializedRequests('skill'));
		$this->cvs = $this->getCvs();
		$this->template->pageParams = $this->getPagination();
		$this->template->cvs = $this->cvs;
		parent::render();
	}

	public function handlePagination($page)
	{
		$this->current = $page;
		$this->redrawControl();
	}

	public function handleChangePagination($count)
	{
		$this->countPerPage = $count;
	}

	public function handleResetFilter($filter)
	{
		$this->resetFilter($filter);
		$this->redrawControl();
	}

	public function resetFilter($filter = null)
	{
		if ($filter) {
			if (isset($this->session[$filter])) {
				$this->session[$filter] = [];
			}
		} else {
			$this->session->remove();
		}
	}

	private function persistFilter($filter, $value)
	{
		$this->session[$filter] = $value;
	}

	private function getSerializedRequests($filter = null)
	{
		$result = [];
		$result['skill'] = isset($this->session['skill']) ? $this->session['skill'] : [];
		$result['location'] = isset($this->session['location']) ? $this->session['location'] : [];
		$result['category'] = isset($this->session['category']) ? $this->session['category'] : [];
		$result['search'] = isset($this->session['search']) ? $this->session['search'] : null;

		if ($filter) {
			return $result[$filter];
		} else {
			return $result;
		}
	}

	private function getRequests()
	{
		$requests = $this->getSerializedRequests();
		$requests['skill'] = $this->skillRequests;
		return $requests;
	}

	private function getPagination()
	{
		$cvRep = $this->em->getRepository(Cv::getClassName());
		$count = $cvRep->countOfCvs($this->getRequests());
		$pages = Helpers::pagination($count, $this->countPerPage, $this->current, 4);
		$last = ceil($count / $this->countPerPage);
		$parameters = [
			'current' => $this->current,
			'pages' => $pages,
			'last' => $last,
			'availableCounts' => array_diff(self::$pagination, [$this->countPerPage]),
			'countPerPage' => $this->countPerPage
		];
		if ($this->current > 1) {
			$parameters['previous'] = $this->current - 1;
		}
		if ($this->current < $last) {
			$parameters['next'] = $this->current + 1;
		}
		return $parameters;
	}

	private function getCvs()
	{
		$cvRep = $this->em->getRepository(Cv::getClassName());
		$offset = $this->countPerPage * ($this->current - 1);
		return $cvRep->findByRequests($this->getRequests(), $offset, $this->countPerPage);
	}

	public function createComponentMatchingControl()
	{
		$control = $this->matchingFactory->create();
		return $control;
	}

	public function createComponentSkillsFilter()
	{
		$control = $this->skillsFilterFactory->create();
		$control->setAjax(TRUE, TRUE);
		$control->onAfterSend = function ($skillRequests) {
			$this->persistFilter('skill', $skillRequests);
			$this->redrawControl();
		};
		return $control;
	}

	public function createComponentCategoryFilter()
	{
		$control = $this->jobCategoryFilterFactory->create();
		$control->setCategoryRequests($this->getSerializedRequests('category'));
		$control->onAfterSend = function (array $categoryRequests) {
			$this->persistFilter('category', $categoryRequests);
			$this->redrawControl();
		};
		return $control;
	}

	public function createComponentLocationFilter()
	{
		$control = $this->locationFilterFactory->create();
		$control->setLocationRequests($this->getSerializedRequests('location'));
		$control->onAfterSend = function (array $locationRequests) {
			$this->persistFilter('location', $locationRequests);
			$this->redrawControl();
		};
		return $control;
	}

	public function createComponentSearchFilter()
	{
		$control = $this->searchFilterFactory->create();
		$control->setSearchRequest($this->getSerializedRequests('search'));
		$control->onAfterSend = function ($searchRequest) {
			$this->persistFilter('search', $searchRequest);
			$this->redrawControl();
		};
		return $control;
	}

	public function createComponentCandidatePreview()
	{
		return new Multiplier(function ($cvId) {
			$cv = ArrayUtils::searchByProperty($this->cvs, 'id', $cvId);
			$control = $this->candidatePreviewFactory->create();
			$control->setCv($cv);
			return $control;
		});
	}
}

interface ICandidateGalleryViewFactory
{

	/** @return CandidateGalleryView */
	function create();
}