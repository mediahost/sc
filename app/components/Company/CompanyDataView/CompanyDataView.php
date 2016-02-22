<?php

namespace App\Components\Company;

/**
 * Description of CompanyDataView
 *
 */
class CompanyDataView extends \App\Components\BaseControl 
{
	/** @var \App\Model\Facade\CompanyFacade */
	private $companyFacade;
	
	/** @var int */
	private $userId;
	
	
	/**
	 * 
	 * @param \App\Model\Facade\CompanyFacade $companyFacade
	 */
	public function __construct(\App\Model\Facade\CompanyFacade $companyFacade) {
		parent::__construct();
		$this->companyFacade = $companyFacade;
	}
	
	/**
	 * @inheritdoc
	 */
	public function render() {
		$this->template->companies = $this->loadData();
		parent::render();
	}
	
	private function loadData() {
		if($this->userId) {
			return $this->companyFacade->findByUser($this->userId);
		} else {
			return $this->companyFacade->findAll();
		}
	}
}


/**
 * Definition ICompanyDataViewFactory
 * 
 */
interface ICompanyDataViewFactory
{

	/** @return CompanyDataView */
	function create();
}

