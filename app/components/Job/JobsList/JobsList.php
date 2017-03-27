<?php

namespace App\Components\Grids\Job;

use App\Components\Cv\ISkillsFilterFactory;
use App\Components\Cv\SkillsFilter;
use App\Components\Job\IJobCategoryFilterFactory;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Candidate;
use App\Model\Entity\Job;
use App\Model\Entity\JobCategory;
use App\Model\Entity\Match;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillKnowRequest;
use App\Model\Entity\SkillLevel;
use App\Model\Facade\CandidateFacade;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
use Grido\Components\Paginator;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\QueryBuilder;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Utils\ArrayHash;

class JobsList extends Control
{

	const FILTER_SEARCH = 'search';
	const FILTER_CATEGORIES = 'categories';
	const FILTER_SKILLS = 'skills';
	const FILTER_INVITATIONS = 'onlyInvitations';
	const FILTER_APPLIED = 'onlyAppliedFor';

	/** @var int @persistent */
	public $page = 1;

	/** @var array @persistent */
	public $filter = [];

	/** @var EntityManager @inject */
	public $em;

	/** @var Translator @inject */
	public $translator;

	/** @var \Nette\Security\User @inject */
	public $user;

	/** @var Session @inject */
	public $session;

	/** @var CandidateFacade @inject */
	public $candidateFacade;

	/** @var IJobCategoryFilterFactory @inject */
	public $jobCategoryFilterFactory;

	/** @var ISkillsFilterFactory @inject */
	public $skillsFilterFactory;

	/** @var QueryBuilder */
	protected $qb;

	/** @var array */
	protected $queryParams = [];

	/** @var Paginator */
	protected $paginator;

	/** @var int */
	protected $perPage = 18;

	/** @var Candidate */
	protected $candidate;

	/** @var bool */
	protected $showFilter = FALSE;

	/** @var bool */
	protected $showPaginator = FALSE;

	/** @var bool */
	protected $showRejected = FALSE;

	/** @var bool */
	protected $onlyApproved = FALSE;

	/** @var bool */
	protected $onlyApplied = FALSE;

	/** @var bool */
	protected $onlyMatched = FALSE;

	/** @var string */
	protected $noMatchText;

	private function getModel()
	{
		if (!$this->qb) {
			$jobRepo = $this->em->getRepository(Job::getClassName());
			$this->qb = $jobRepo->createQueryBuilder('j')
				->select('j');

			$this->applyFilter();
		}
		return $this->qb;
	}

	/** @return SessionSection */
	private function getSession()
	{
		return $this->session->getSection(get_class($this) . get_class($this->presenter));
	}

	private function applyFilter()
	{
		$this->applyMatched();
		$this->filterFulltext();
		$this->filterCategories();
		$this->filterSkills();
		$this->qb->setParameters($this->queryParams);
	}

	private function applyMatched()
	{
		if ($this->getSerializedFilter(self::FILTER_INVITATIONS)) {
			$this->onlyApproved = TRUE;
			$this->onlyApplied = FALSE;
			$this->onlyMatched = FALSE;
		}
		if ($this->getSerializedFilter(self::FILTER_APPLIED)) {
			$this->onlyApproved = FALSE;
			$this->onlyApplied = TRUE;
			$this->onlyMatched = TRUE;
		}

		if ($this->onlyApplied || $this->onlyApproved || $this->onlyMatched) {
			$this->qb->join(Match::getClassName(), 'm', 'WITH', 'j = m.job');

			$params = [
				'candidate' => $this->candidate,
				'on' => TRUE,
			];
			$conditions = new Orx();
			if ($this->onlyApplied) {
				$conditions->add('m.candidate = :candidate AND m.candidateApprove = :on AND m.adminApprove = :off');
				$params['off'] = FALSE;
			}
			if ($this->onlyApproved) {
				$conditions->add('m.candidate = :candidate AND m.adminApprove = :on AND m.candidateApprove = :off');
				$params['off'] = FALSE;
			}
			if ($this->onlyMatched) {
				$conditions->add('m.candidate = :candidate AND m.candidateApprove = :on AND m.adminApprove = :on');
			}
			if ($conditions->count()) {
				$this->qb->andWhere($conditions);
				$this->queryParams = $this->queryParams + $params;
			}
		}
	}

	private function filterFulltext()
	{
		$fulltextValue = $this->getSerializedFilter(self::FILTER_SEARCH);
		if ($fulltextValue) {
			$words = preg_split('/\s+/', $fulltextValue, -1, PREG_SPLIT_NO_EMPTY);

			$rules = ['j.name LIKE',];
			$params = [];
			$conditions = new Andx();
			foreach ($words as $i => $word) {
				$partOr = new Orx();
				foreach ($rules as $rule) {
					$partOr->add($rule . ' :word' . $i);
				}
				$conditions->add($partOr);
				$params['word' . $i] = '%' . $word . '%';
			}

			if ($conditions->count()) {
				$this->qb->andWhere($conditions);
				$this->queryParams = $this->queryParams + $params;
			}
		}
	}

	private function filterCategories()
	{
		$categories = $this->getSerializedFilter(self::FILTER_CATEGORIES);
		if (is_array($categories) && count($categories)) {
			$categoryRepo = $this->em->getRepository(JobCategory::getClassName());
			$params = [];
			$conditions = new Orx();
			foreach ($categories as $categoryId => $categoryName) {
				$category = $categoryRepo->find($categoryId);
				if ($category) {
					$conditions->add('j.category = :category' . $categoryId);
					$params['category' . $categoryId] = $category;
				}
			}
			if ($conditions->count()) {
				$this->qb->andWhere($conditions);
				$this->queryParams = $this->queryParams + $params;
			}
		}
	}

	private function filterSkills()
	{
		$skills = $this->getSerializedFilter(self::FILTER_SKILLS);
		if (is_array($skills) && array_key_exists('skillRange', $skills) && array_key_exists('yearRange', $skills)) {
			$skillRanges = $skills['skillRange'];
			$yearRanges = $skills['yearRange'];
			if ($skillRanges && $yearRanges) {
				$skillRepo = $this->em->getRepository(Skill::getClassName());
				$skillLevelRepo = $this->em->getRepository(SkillLevel::getClassName());
				$params = [];
				$conditions = new Andx();
				foreach ($skillRanges as $id => $skillRange) {
					$skill = $skillRepo->find($id);
					if ($skill) {
						$yearRange = array_key_exists($id, $yearRanges) ? $yearRanges[$id] : '1,10';
						if ($yearRange && preg_match('/^(\d+)\,(\d+)$/', $yearRange, $matches)) {
							$yearLow = (int)$matches[1];
							$yearHigh = (int)$matches[2];
						}
						if ($skillRange && preg_match('/^(\d+)\,(\d+)$/', $skillRange, $matches)) {
							$skillLow = (int)$matches[1];
							$skillHigh = (int)$matches[2];
						}
						if ($skillLow !== SkillLevel::FIRST_PRIORITY || $skillHigh !== SkillLevel::LAST_PRIORITY) {
							$condition = new Andx();
							$condition->add('r.skill = :skill' . $id);
							$params['skill' . $id] = $skill;

							$skillLevelFrom = $skillLevelRepo->find($skillLow);
							$skillLevelTo = $skillLevelRepo->find($skillHigh);
							if ($skillLevelFrom && $skillLevelTo) {
								$condition->add('r.levelFrom <= :levelFrom' . $id);
								$condition->add('r.levelTo >= :levelTo' . $id);
								$params['levelFrom' . $id] = $skillLevelFrom;
								$params['levelTo' . $id] = $skillLevelTo;
							}

							if ($yearLow !== 1) {
								$condition->add('r.yearFrom <= :yearFrom' . $id);
								$params['yearFrom' . $id] = $yearLow;
							}
							if ($yearHigh !== 10) {
								$condition->add('r.yearTo >= :yearTo' . $id);
								$params['yearTo' . $id] = $yearHigh;
							}

							$conditions->add($condition);
						}
					}
				}
				if ($conditions->count()) {
					$this->qb->join(SkillKnowRequest::getClassName(), 'r', 'WITH', 'j = r.job');
					$this->qb->andWhere($conditions);
					$this->queryParams = $this->queryParams + $params;
				}
			}
		}
	}

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
		$result[self::FILTER_INVITATIONS] = isset($session[self::FILTER_INVITATIONS]) ? $session[self::FILTER_INVITATIONS] : FALSE;
		$result[self::FILTER_APPLIED] = isset($session[self::FILTER_APPLIED]) ? $session[self::FILTER_APPLIED] : FALSE;
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
		$skillRepo = $this->em->getRepository(Skill::getClassName());
		$skillLevelRepo = $this->em->getRepository(SkillLevel::getClassName());
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
						if ($separatedYearsValues[1] == SkillsFilter::YEARS_MAX) {
							$separatedYearsValues[1] .= SkillsFilter::YEARS_POSTFIX;
						}
						$loaded[$skillId] .= ' | ' . $separatedYearsValues[0] . '-' . $separatedYearsValues[1] . ' years';
					}
					$loaded[$skillId] .= ')';
				}
			}
		}
		return $loaded;
	}

	public function isFiltered()
	{
		$seralized = $this->getSerializedFilter();
		$isSerializedFilled = $seralized[self::FILTER_SEARCH] ||
			$seralized[self::FILTER_INVITATIONS] ||
			$seralized[self::FILTER_APPLIED] ||
			count($seralized[self::FILTER_SKILLS]) ||
			count($seralized[self::FILTER_CATEGORIES]);
		return count($this->filter) || $isSerializedFilled;
	}

	public function getJobs()
	{
		$paginator = $this->getPaginator()
			->setItemCount($this->getCount())
			->setPage($this->page);

		$model = $this->getModel();
		$model->setMaxResults($paginator->getLength());
		$model->setFirstResult($paginator->getOffset());

		$data = [];
		$result = $model->getQuery()->getResult();
		foreach ($result as $item) {
			$data[] = is_array($item) ? $item[0] : $item;
		}
		return $data;
	}

	public function getCount()
	{
		$model = $this->getModel();
		/* @var $countQuery Query */
		$countQuery = clone $model;

		$countQuery->setParameters(clone $model->getParameters());

		$countQuery->setFirstResult(null)->setMaxResults(null);

		$countQuery->resetDQLPart('select')
			->select('COUNT(j) AS counter');

		return (int)$countQuery->getQuery()->getSingleScalarResult();
	}

	public function setCandidate(Candidate $candidate)
	{
		$this->candidate = $candidate;
		return $this;
	}

	public function setOnlyApproved()
	{
		$this->onlyApproved = TRUE;
		return $this;
	}

	public function setOnlyApplied()
	{
		$this->onlyApplied = TRUE;
		return $this;
	}

	public function setOnlyMatched()
	{
		$this->onlyMatched = TRUE;
		return $this;
	}

	public function setShowFilter($value = TRUE)
	{
		$this->showFilter = $value;
		return $this;
	}

	public function setShowPaginator($value = TRUE)
	{
		$this->showPaginator = $value;
		return $this;
	}

	public function setShowRejected($value = TRUE)
	{
		$this->showRejected = $value;
		return $this;
	}

	public function setNoMatchText($value)
	{
		$this->noMatchText = $value;
		return $this;
	}

	public function handlePage($page)
	{
		$this->page = $page;
		$this->reload();
	}

	public function handleFilter(SubmitButton $button)
	{
		$values = $button->form->values;

		$this->persistFilter(self::FILTER_SEARCH, $values->fulltext);
		$this->persistFilter(self::FILTER_INVITATIONS, !!$values->onlyInvitations);
		$this->persistFilter(self::FILTER_APPLIED, !!$values->onlyAppliedFor);

		$this->page = 1;
		$this->reload();
	}

	public function handleReset(SubmitButton $button)
	{
		$this->resetFilter();

		$button->form->setValues([], TRUE);

		$this->handlePage(1);
	}

	public function handleResetFilter($part)
	{
		$this->resetFilter($part);
		$this->redirect('this');
	}

	public function reload()
	{
		if ($this->presenter->isAjax()) {
			$this->presenter->redrawControl();
			$this->redrawControl();
		} else {
			$this->redirect('this');
		}
	}

	public function render()
	{
		if ($this->presenter->isAjax()) {
			if ($this->isControlInvalid('jobList')) {
				$this->renderList();
			}
			if ($this->isControlInvalid('jobFilter')) {
				$this->renderFilter();
			}
		} else {
			$this->renderList();
		}
	}

	public function renderList()
	{
		$this->template->jobs = $this->getJobs();
		$this->template->paginator = $this->getPaginator();
		$this->template->candidate = $this->candidate;
		$this->template->candidateFacade = $this->candidateFacade;
		$this->template->showRejected = $this->showRejected;
		$this->template->showPaginator = $this->showPaginator;
		$this->template->noMatchText = $this->noMatchText;
		$this->templateRender('list');
	}

	public function renderFilter()
	{
		$this->template->selectedSkills = $this->loadSerializedSkills();
		$this->template->selectedCategories = $this->getSerializedFilter(self::FILTER_CATEGORIES);
		$this->template->showFilter = $this->showFilter;
		$this->templateRender('filter');
	}

	private function templateRender($template = 'default')
	{
		$dir = dirname($this->getReflection()->getFileName());
		$this->template->setFile($dir . '/' . $template . '.latte');
		$this->template->render();
	}

	public function createTemplate()
	{
		$template = parent::createTemplate();
		$template->registerHelper('translate', callback($this->translator, 'translate'));

		return $template;
	}

	public function getPaginator()
	{
		if ($this->paginator === NULL) {
			$this->paginator = new Paginator();
			$this->paginator->setItemsPerPage($this->perPage);
		}

		return $this->paginator;
	}

	protected function createComponentForm($name)
	{
		$form = new Form($this, $name);
		$form->setTranslator($this->translator);
		$form->setRenderer(new MetronicFormRenderer());

		$form->addText('fulltext', 'Fulltext')
			->setAttribute('placeholder', 'Search Keywords')
			->getControlPrototype()->class = 'form-control text-filter';

		$categories = [];
		$form->addSelect('category', 'Category', $categories);

		$form->addCheckbox('onlyInvitations', 'Only Invitations');
		$form->addCheckbox('onlyAppliedFor', 'Only Applied For');

		$button = $form->addSubmit('search', 'Find Job');
		$button->onClick[] = $this->handleFilter;
		$button->getControlPrototype()->class = 'btn btn-primary';

		$button = $form->addSubmit('reset', 'Reset');
		$button->getControlPrototype()->class = 'btn btn-default';
		$button->onClick[] = $this->handleReset;

		$form->setDefaults($this->getDefaults());
	}

	private function getDefaults()
	{
		$values = [
			'fulltext' => $this->getSerializedFilter(self::FILTER_SEARCH),
			'onlyInvitations' => $this->getSerializedFilter(self::FILTER_INVITATIONS),
			'onlyAppliedFor' => $this->getSerializedFilter(self::FILTER_APPLIED),
		];
		return $values;
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

}

interface IJobsListFactory
{

	/** @return JobsList */
	function create();
}
