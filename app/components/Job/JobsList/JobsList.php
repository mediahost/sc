<?php

namespace App\Components\Grids\Job;

use App\Components\BaseControl;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Candidate;
use App\Model\Entity\Job;
use App\Model\Entity\Match;
use App\Model\Facade\CandidateFacade;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
use Grido\Components\Paginator;
use Kdyby\Doctrine\QueryBuilder;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;

class JobsList extends BaseControl
{

	/** @var int @persistent */
	public $page = 1;

	/** @var array @persistent */
	public $filter = [];

	/** @var \Nette\Security\User @inject */
	public $user;

	/** @var CandidateFacade @inject */
	public $candidateFacade;

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

	private function applyFilter()
	{
		$this->applyMatched();
		$this->filterFulltext();
		$this->qb->setParameters($this->queryParams);
	}

	private function applyMatched()
	{
		if (array_key_exists('onlyInvitations', $this->filter) && $this->filter['onlyInvitations']) {
			$this->onlyApproved = TRUE;
			$this->onlyApplied = FALSE;
			$this->onlyMatched = FALSE;
		}
		if (array_key_exists('onlyAppliedFor', $this->filter) && $this->filter['onlyAppliedFor']) {
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
		if (array_key_exists('fulltext', $this->filter) && $this->filter['fulltext']) {
			$words = preg_split('/\s+/', $this->filter['fulltext'], -1, PREG_SPLIT_NO_EMPTY);

			$rules = [
				'j.name LIKE',
			];
			$params = [];
			$conditions = new Andx();
			foreach ($words as $i => $word) {
				$partOr = new Orx();
				foreach ($rules as $rule) {
					$partOr->add($rule . ' :word' . $i);
				}
				$conditions->add($partOr);
				$this->queryParams['word' . $i] = '%' . $word . '%';
			}

			if ($conditions->count()) {
				$this->qb->andWhere($conditions);
				$this->queryParams = $this->queryParams + $params;
			}
		}
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

		$this->filter['fulltext'] = urlencode($values->fulltext);
		$this->filter['onlyInvitations'] = !!$values->onlyInvitations;
		$this->filter['onlyAppliedFor'] = !!$values->onlyAppliedFor;

		$this->page = 1;
		$this->reload();
	}

	public function handleReset(SubmitButton $button)
	{
		$this->filter = [];

		$button->form->setValues([], TRUE);

		$this->handlePage(1);
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
		$this->template->jobs = $this->getJobs();
		$this->template->paginator = $this->getPaginator();
		$this->template->candidate = $this->candidate;
		$this->template->candidateFacade = $this->candidateFacade;
		$this->template->showRejected = $this->showRejected;
		$this->template->showPaginator = $this->showPaginator;
		$this->template->noMatchText = $this->noMatchText;
		$this->templateRender();
	}

	public function renderFilter()
	{
		$this->template->showFilter = $this->showFilter;
		$this->templateRender('filter');
	}

	private function templateRender($template = self::DEFAULT_TEMPLATE)
	{
		$this->setTemplateFile($template);
		parent::render();
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
			->getControlPrototype()->class = 'form-control';

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
		$values = [];
		foreach ($this->filter as $key => $item) {
			switch ($key) {
				case 'fulltext':
					$values[$key] = $item;
					break;
				case 'onlyInvitations':
				case 'onlyAppliedFor':
					$values[$key] = !!$item;
					break;
			}
		}
		return $values;
	}

}

interface IJobsListFactory
{

	/** @return JobsList */
	function create();
}
