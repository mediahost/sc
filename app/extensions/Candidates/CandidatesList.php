<?php

namespace App\Extensions\Candidates;

use App\Components\Candidate\Form\IPrintCandidateFactory;
use App\Extensions\Candidates\Components\DataHolder;
use App\Extensions\Candidates\Components\IProducerFilterFactory;
use App\Extensions\Candidates\Components\ISortingFormFactory;
use App\Extensions\Candidates\Components\Paginator;
use App\Forms\Form;
use App\Forms\Renderers\MetronicFormRenderer;
use App\Model\Entity\Category;
use App\Model\Entity\Parameter;
use App\Model\Entity\Producer;
use App\Model\Entity\ProducerLine;
use App\Model\Entity\ProducerModel;
use App\Model\Entity\Product;
use App\Model\Facade\BasketFacade;
use App\Model\Facade\ProductFacade;
use App\Model\Facade\StockFacade;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Translation\Translator;
use Nette\Application\UI\Control;
use Nette\Application\UI\Multiplier;
use Nette\Localization\ITranslator;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

class CandidatesList extends Control
{

	const SORT_BY_NAME_ASC = 1;
	const SORT_BY_NAME_DESC = 2;

	// <editor-fold defaultstate="collapsed" desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var Translator @inject */
	public $translator;

	/** @var IPrintCandidateFactory @inject */
	public $iCandidatePrint;

	/** @var ISortingFormFactory @inject */
	public $iSortingFormFactory;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="persistent">

	/** @var int @persistent */
	public $page = 1;

	/** @var int @persistent */
	public $sorting = self::SORT_BY_NAME_ASC;

	/** @var int @persistent */
	public $perPage = 15;

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

	/** @var array */
	protected $perPageListMultiples = [1, 2, 3, 6];

	/** @var array */
	protected $perPageList = [16, 32, 48, 96];

	/** @var int */
	protected $itemsPerRow = 3;

	/** @var int */
	protected $rowsPerPage = 3;

	/** @var bool */
	protected $ajax;

	// </editor-fold>

	private function getHolder()
	{
		if (!$this->holder) {
			$this->holder = new DataHolder($this->em);
		}
		return $this->holder;
	}

	/* 	 ADD FILTERS *************************************************************************************** */

	// <editor-fold defaultstate="collapsed" desc="add filters">

	public function addFilterFulltext($text)
	{
		$this->getHolder()->filterFulltext($text);
		return $this;
	}

	// </editor-fold>

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
			case self::SORT_BY_NAME_ASC:
			case self::SORT_BY_NAME_DESC:
				$dir = $this->sorting === self::SORT_BY_NAME_ASC ? 'ASC' : 'DESC';
				$this->getHolder()->setSorting(DataHolder::ORDER_BY_NAME, $dir);
				break;
		}
		return $this;
	}

	protected function applyFiltering()
	{
		$this->getHolder()->filterNotEmpty();
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
			case self::SORT_BY_NAME_ASC:
			case self::SORT_BY_NAME_DESC:
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
			'sendOnChange',
			!$this->ajax ?: 'ajax'
		];

		$defaultValues = [];
		$form->setDefaults($defaultValues);

		$form->onSuccess[] = $this->processFilterForm;
	}

	public function processFilterForm(Form $form, ArrayHash $values)
	{
		$this->reload();
	}

	// </editor-fold>
}

interface ICandidatesListFactory
{

	/** @return CandidatesList */
	function create();
}
