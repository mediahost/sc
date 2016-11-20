<?php

namespace App\Extensions\Candidates;

use App\Components\Candidate\Form\IPrintCandidateFactory;
use App\Components\Cv\ISkillsFilterFactory;
use App\Components\Cv\SkillsFilter;
use App\Components\Job\IJobCategoryFilterFactory;
use App\Extensions\Candidates\Components\DataHolder;
use App\Extensions\Candidates\Components\ISortingFormFactory;
use App\Extensions\Candidates\Components\Paginator;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Job;
use App\Model\Entity\JobCategory;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillLevel;
use App\Model\Entity\User as UserEntity;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Control;
use Nette\Application\UI\Multiplier;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Localization\ITranslator;
use Nette\Security\User;
use Nette\Utils\ArrayHash;

class CandidatesList extends Control
{

	const SORT_BY_ID_ASC = 1;
	const SORT_BY_ID_DESC = 2;
	const FILTER_PART_COMPANY = 'company';
	const FILTER_SEARCH = 'search';
	const FILTER_JOB = 'job';
	const FILTER_MANAGER = 'manager';
	const FILTER_CATEGORIES = 'categories';
	const FILTER_SKILLS = 'skills';

	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var User @inject */
	public $user;

	/** @var Translator @inject */
	public $translator;

	/** @var IPrintCandidateFactory @inject */
	public $iCandidatePrint;

	/** @var ISortingFormFactory @inject */
	public $iSortingFormFactory;

	/** @var IJobCategoryFilterFactory @inject */
	public $jobCategoryFilterFactory;

	/** @var ISkillsFilterFactory @inject */
	public $skillsFilterFactory;

	/** @var Session @inject */
	public $session;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="persistent">

	/** @var int @persistent */
	public $page = 1;

	/** @var int @persistent */
	public $sorting = self::SORT_BY_ID_DESC;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var DataHolder */
	protected $holder;

	/** @var mixed */
	protected $data;

	/** @var int total count of items */
	protected $count;

	/** @var Paginator */
	protected $paginator;

	/** @var int */
	protected $perPage = 16;

	/** @var array */
	protected $perPageListMultiples = [1, 2, 3, 6];

	/** @var array */
	protected $perPageList = [16, 32, 48, 96];

	/** @var int */
	protected $itemsPerRow = 4;

	/** @var int */
	protected $rowsPerPage = 4;

	/** @var bool */
	protected $ajax;

	/** @var array */
	protected $filterDeny = [];

	/** @var Job */
	protected $selectedJob;

	/** @var UserEntity */
	protected $selectedManager;

	/** @var bool */
	protected $showAsCompany = FALSE;

	/** @var bool */
	protected $prefilteredJob = FALSE;

	/** @var array */
	protected $candidateOnReload = [];

	// </editor-fold>

	private function getHolder()
	{
		if (!$this->holder) {
			$this->holder = new DataHolder($this->em);
		}
		return $this->holder;
	}

	/** @return SessionSection */
	private function getSession()
	{
		return $this->session->getSection(get_class($this) . get_class($this->presenter));
	}

	/* 	 ADD FILTERS *************************************************************************************** */

	// <editor-fold defaultstate="collapsed" desc="add filters">

	public function addFilterJob(Job $job, $showAsCompany = FALSE, $state = NULL)
	{
		$this->filterDeny[self::FILTER_PART_COMPANY] = TRUE;
		$this->selectedJob = $job;
		$this->prefilteredJob = TRUE;
		$this->showAsCompany = $showAsCompany;
		$this->getHolder()->filterJob($job, $state, $showAsCompany);
		return $this;
	}

	// </editor-fold>

	protected function persistFilter($filter, $value)
	{
		$session = $this->getSession();
		$session[$filter] = $value;
	}

	protected function resetFilter($filter = null)
	{
		$session = $this->getSession();
		if ($filter) {
			if (isset($session[$filter])) {
				unset($session[$filter]);
			}
		} else {
			$session->remove();
		}
	}

	protected function getSerializedFilter($filter = null)
	{
		$session = $this->getSession();
		$result = [];
		$result[self::FILTER_SEARCH] = isset($session[self::FILTER_SEARCH]) ? $session[self::FILTER_SEARCH] : NULL;
		$result[self::FILTER_JOB] = isset($session[self::FILTER_JOB]) ? $session[self::FILTER_JOB] : NULL;
		$result[self::FILTER_MANAGER] = isset($session[self::FILTER_MANAGER]) ? $session[self::FILTER_MANAGER] : NULL;
		$result[self::FILTER_SKILLS] = isset($session[self::FILTER_SKILLS]) ? $session[self::FILTER_SKILLS] : [];
		$result[self::FILTER_CATEGORIES] = isset($session[self::FILTER_CATEGORIES]) ? $session[self::FILTER_CATEGORIES] : [];

		if ($filter) {
			return $result[$filter];
		} else {
			return $result;
		}
	}

	protected function loadSerializedSkills()
	{
		$defaultRange = SkillsFilter::getDefaultRangeValue();
		$defaultYears = SkillsFilter::getDefaultYearsValue();
		$skillRepo = $this->em->getDao(Skill::getClassName());
		$skillLevelRepo = $this->em->getDao(SkillLevel::getClassName());
		$loaded = [];
		$serializedSkills = $this->getSerializedFilter(self::FILTER_SKILLS);
		if (isset($serializedSkills['skillRange'])) {
			foreach ($serializedSkills['skillRange'] as $skillId => $skillValues) {
				if ($skillValues !== $defaultRange) {
					$loadedSkill = $skillRepo->find($skillId);
					$separatedSkillValues = SkillsFilter::separateValues($skillValues);
					$skillFrom = $skillLevelRepo->find($separatedSkillValues[0]);
					$skillTo = $skillLevelRepo->find($separatedSkillValues[1]);
					$loaded[$skillId] = (string)$loadedSkill . ' (' . $skillFrom . ' - ' . $skillTo;
					if ($serializedSkills['yearRange'][$skillId] !== $defaultYears) {
						$separatedYearsValues = SkillsFilter::separateValues($serializedSkills['yearRange'][$skillId]);
						$loaded[$skillId] .= ' | ' . $separatedYearsValues[0] . '-' . $separatedYearsValues[1] . ' years';
					}
					$loaded[$skillId] .= ')';
				}
			}
		}
		return $loaded;
	}

	protected function applyPaging()
	{
		$paginator = $this->getPaginator()
			->setItemCount($this->getCount())
			->setPage($this->page);

		$offset = $paginator->getOffset();
		$limit = $paginator->getLength();
		$this->getHolder()->setPaging($limit, $offset);
		return $this;
	}

	protected function applySorting()
	{
		switch ($this->sorting) {
			case self::SORT_BY_ID_ASC:
			case self::SORT_BY_ID_DESC:
				$dir = $this->sorting === self::SORT_BY_ID_ASC ? 'ASC' : 'DESC';
				$this->getHolder()->setSorting(DataHolder::ORDER_BY_ID, $dir);
				break;
		}
		return $this;
	}

	protected function applyFiltering()
	{
		$this->getHolder()->filterNotEmpty();

		$fulltext = $this->getSerializedFilter(self::FILTER_SEARCH);
		$this->getHolder()->filterFulltext($fulltext);

		$jobId = $this->getSerializedFilter(self::FILTER_JOB);
		if ($jobId) {
			$jobRepo = $this->em->getRepository(Job::getClassName());
			$job = $jobRepo->find($jobId);
			if ($job) {
				$this->selectedJob = $job;
				$this->getHolder()->filterJob($job);
			}
		}

		$managerId = $this->getSerializedFilter(self::FILTER_MANAGER);
		if ($managerId) {
			$userRepo = $this->em->getRepository(UserEntity::getClassName());
			$manager = $userRepo->find($managerId);
			if ($manager) {
				$jobRepo = $this->em->getRepository(Job::getClassName());
				$jobs = $jobRepo->findBy([
					'accountManager' => $manager,
				]);
				$this->selectedManager = $manager;
				$this->getHolder()->filterJob($jobs);
			}
		}

		$filterCategories = $this->getSerializedFilter(self::FILTER_CATEGORIES);
		$categoriesIds = array_keys($filterCategories);
		$categories = [];
		if (count($categoriesIds)) {
			$jobCategoryRepo = $this->em->getRepository(JobCategory::getClassName());
			foreach ($categoriesIds as $categoryId) {
				$category = $jobCategoryRepo->find($categoryId);
				if ($category) {
					$categories[$categoryId] = $category;
				}
			}
		}
		$this->getHolder()->filterCategories($categories);

		$serializedSkills = $this->getSerializedFilter(self::FILTER_SKILLS);
		$defaultRange = SkillsFilter::getDefaultRangeValue();
		$defaultYears = SkillsFilter::getDefaultYearsValue();
		$skills = [
			'skillRange' => [],
			'yearRange' => [],
		];
		if (isset($serializedSkills['skillRange'])) {
			foreach ($serializedSkills['skillRange'] as $skillId => $skillValues) {
				if ($skillValues !== $defaultRange) {
					$skills['skillRange'][$skillId] = SkillsFilter::separateValues($skillValues);
					if ($serializedSkills['yearRange'][$skillId] !== $defaultYears) {
						$skills['yearRange'][$skillId] = SkillsFilter::separateValues($serializedSkills['yearRange'][$skillId]);
					}
				}
			}
		}
		$this->getHolder()->filterItSkills($skills);

		return $this;
	}

	/* 	 SETTERS ******************************************************************************************* */

	// <editor-fold defaultstate="collapsed" desc="public setters">

	public function setPage($page, $itemsPerPage = NULL)
	{
		$this->page = $page;
		$this->perPage = $itemsPerPage;
		return $this;
	}

	public function setSorting($sort)
	{
		switch ($sort) {
			case self::SORT_BY_ID_ASC:
			case self::SORT_BY_ID_DESC:
				$this->sorting = $sort;
				break;
		}

		return $this;
	}

	public function setItemsPerPage($itemsPerRow, $rowsPerPage = 1)
	{
		$itemsPerRowInt = (int)$itemsPerRow;
		$rowsPerPageInt = (int)$rowsPerPage;
		$this->itemsPerRow = $itemsPerRowInt ? $itemsPerRowInt : 1;
		$this->rowsPerPage = $rowsPerPageInt ? $rowsPerPageInt : 1;

		$itemsPerPage = $this->getDefaultPerPage();
		$this->perPage = $itemsPerPage;

		$this->resetPerPageList($itemsPerPage);

		return $this;
	}

	public function setTranslator(ITranslator $translator)
	{
		$this->translator = $translator;
		return $this;
	}

	public function setAjax($value = TRUE)
	{
		$this->ajax = $value;
		return $this;
	}

	public function setCandidateOnReload($callable)
	{
		$this->candidateOnReload = $callable;
		return $this;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="protected setters">

	protected function resetPerPageList($firstItem)
	{
		$this->perPageList = $this->perPageListMultiples;
		foreach ($this->perPageList as $key => $value) {
			$this->perPageList[$key] = $firstItem * $value;
		}

		return $this;
	}

	// </editor-fold>

	/* 	 GETTERS ******************************************************************************************* */

	// <editor-fold defaultstate="collapsed" desc="public getters">

	public function getPerPage()
	{
		return $this->perPage === NULL ? $this->getDefaultPerPage() : $this->perPage;
	}

	public function getPerPageList()
	{
		return $this->perPageList;
	}

	public function getPaginator()
	{
		if ($this->paginator === NULL) {
			$this->paginator = new Paginator();
			$this->paginator->setItemsPerPage($this->getPerPage());
		}

		return $this->paginator;
	}

	public function getCount($refresh = FALSE)
	{
		if ($this->count === NULL || $refresh) {
			$this->count = $this->getHolder()->getCount();
		}
		return $this->count;
	}

	public function getData($applyPaging = TRUE, $useCache = TRUE)
	{
		$data = $this->data;
		if ($data === NULL || $useCache === FALSE) {

			$this->applyFiltering();
			$this->applySorting();

			if ($applyPaging) {
				$this->applyPaging();
			}

			$data = $this->getHolder()->getCandidates();

			if ($useCache) {
				$this->data = $data;
			}

			if ($applyPaging && $data && !in_array($this->page, range(1, $this->getPaginator()->pageCount))) {
				$this->page = 1;
			}
		}

		return $data;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="protected getters">

	protected function getDefaultPerPage()
	{
		return $this->itemsPerRow * $this->rowsPerPage;
	}

	// </editor-fold>

	/* 	 SIGNALS ******************************************************************************************* */

	// <editor-fold desc="signals">

	public function handlePage($page)
	{
		$this->page = $page;
		$this->reload();
	}

	public function handleResetFilter($filter)
	{
		$this->resetFilter($filter);
		$this->redirect('this');
	}

	public function reload()
	{
		if ($this->presenter->isAjax()) {
			$this->redrawControl();
			$this->presenter->redrawControl();
		} else {
			$this->redirect('this');
		}
	}

	// </editor-fold>

	/* 	 TEMPLATES ***************************************************************************************** */

	// <editor-fold defaultstate="collapsed" desc="templates">

	public function createTemplate()
	{
		$template = parent::createTemplate();
		$template->setFile(__DIR__ . '/templates/candidatesList.latte');
		$template->registerHelper('translate', callback($this->translator, 'translate'));

		return $template;
	}

	public function render()
	{
		if ($this->presenter->isAjax()) {
			if ($this->isControlInvalid('candidatesList')) {
				$this->renderList();
			}
			if ($this->isControlInvalid('candidatesFilter')) {
				$this->renderFilter();
			}
			if ($this->isControlInvalid('candidatesPaginator')) {
				$this->renderPaginator();
			}
			if ($this->isControlInvalid('candidatesSorting')) {
				$this->renderSorting();
			}
		} else {
			$this->renderList();
		}
	}

	public function renderList()
	{
		$this->template->setFile(__DIR__ . '/templates/candidatesList.latte');
		$this->templateRender();
	}

	public function renderFilter()
	{
		$this->template->selectedSkills = $this->loadSerializedSkills();
		$this->template->selectedCategories = $this->getSerializedFilter(self::FILTER_CATEGORIES);
		$this->template->denyCompany = array_key_exists(self::FILTER_PART_COMPANY, $this->filterDeny) && $this->filterDeny[self::FILTER_PART_COMPANY];
		$this->template->setFile(__DIR__ . '/templates/filter.latte');
		$this->templateRender();
	}

	public function renderPaginator()
	{
		$this->template->setFile(__DIR__ . '/templates/paginator.latte');
		$this->templateRender();
	}

	public function renderSorting()
	{
		$this->template->setFile(__DIR__ . '/templates/sorting.latte');
		$this->templateRender();
	}

	private function templateRender()
	{
		$data = $this->getData();

		$this->template->candidates = $data;
		$this->template->paginator = $this->getPaginator();
		$this->template->itemsPerRow = $this->itemsPerRow;
		$this->template->locale = $this->translator->getLocale();
		$this->template->ajax = $this->ajax;
		$this->template->render();
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="forms">

	protected function createComponentCandidate()
	{
		return new Multiplier(function ($itemId) {
			$control = $this->iCandidatePrint->create();
			$control->setCandidateById($itemId);
			$control->setJob($this->selectedJob, $this->prefilteredJob);
			$control->setAccountManager($this->selectedManager);
			$control->setShow($this->user->isAllowed('candidatesList', 'showAll'), $this->showAsCompany);
			$control->onReload = $this->candidateOnReload;
			return $control;
		});
	}

	/** @return SortingForm */
	protected function createComponentSortingForm()
	{
		$control = $this->iSortingFormFactory->create();
		$control->setAjax();
		$control->setSorting($this->sorting);
		$control->setPerPage($this->perPage, $this->perPageList);

		$control->onAfterSend = function ($sorting, $perPage) {
			$this->setSorting($sorting);
			$this->perPage = $perPage;
			$this->reload();
		};
		return $control;
	}

	protected function createComponentFilterForm($name)
	{
		$form = new Form($this, $name);
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());
		$form->getElementPrototype()->class = [
			'ajax'
		];

		$form->addText('search')
			->setAttribute('placeholder', $this->translator->translate('Fulltext search...'))
			->setDefaultValue($this->getSerializedFilter(self::FILTER_SEARCH));

		$jobRepo = $this->em->getRepository(Job::getClassName());
		$jobs = $jobRepo->findPairs('name');
		$form->addSelect('job', 'Job', [NULL => '--- All Jobs ---'] + $jobs)
			->setDefaultValue($this->getSerializedFilter(self::FILTER_JOB));

		$userRepo = $this->em->getRepository(UserEntity::getClassName());
		$managers = $userRepo->findAccountManagers();
		$form->addSelect('manager', 'Account manager', [NULL => '--- All Managers ---'] + $managers)
			->setDefaultValue($this->getSerializedFilter(self::FILTER_MANAGER));

		$form->addSubmit('send', 'Search')
			->getControlPrototype()->setClass('loadingOnClick');

		$defaultValues = [];
		$form->setDefaults($defaultValues);

		$form->onSuccess[] = $this->processFilterForm;
	}

	public function processFilterForm(Form $form, ArrayHash $values)
	{
		$this->persistFilter(self::FILTER_SEARCH, $values->search);
		$this->persistFilter(self::FILTER_JOB, $values->job);
		$this->persistFilter(self::FILTER_MANAGER, $values->manager);
		$this->reload();
	}

	public function createComponentCategoryFilter()
	{
		$control = $this->jobCategoryFilterFactory->create();
		$control->setAjax(true, true);
		$control->setCategoryRequests($this->getSerializedFilter(self::FILTER_CATEGORIES));
		$control->onAfterSend = function (array $categoryRequests) {
			$this->persistFilter(self::FILTER_CATEGORIES, $categoryRequests);
			$this->reload();
		};
		return $control;
	}

	public function createComponentSkillsFilter()
	{
		$control = $this->skillsFilterFactory->create();
		$control->setAjax(true, true);
		$control->setSkillRequests(ArrayHash::from($this->getSerializedFilter(self::FILTER_SKILLS)));
		$control->onAfterSend = function ($skillRequests) {
			$this->persistFilter(self::FILTER_SKILLS, (array)$skillRequests);
			$this->reload();
		};
		return $control;
	}

	// </editor-fold>
}

interface ICandidatesListFactory
{

	/** @return CandidatesList */
	function create();
}
