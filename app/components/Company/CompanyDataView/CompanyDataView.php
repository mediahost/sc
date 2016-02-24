<?php

namespace App\Components\Company;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Description of CompanyDataView
 *
 */
class CompanyDataView extends \App\Components\BaseControl 
{
	/** @var ArrayCollection */
	private $companies;
	
	
	/**
	 * @inheritdoc
	 */
	public function render() {
		$this->companies = ($this->companies)  ?  $this->companies  :  new ArrayCollection();
		$this->template->companies = $this->companies;
		parent::render();
	}
	
	/**
	 * @param ArrayCollection $companies
	 * @return \App\Components\Company\CompanyDataView
	 */
	public function setCompanies(ArrayCollection $companies) {
		$this->companies = $companies;
		return $this;
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

