<?php

namespace App\Components\Company;

use Doctrine\Common\Collections\ArrayCollection;
use App\Model\Facade\UserFacade;

/**
 * Description of CompanyDataView
 *
 */
class CompanyDataView extends \App\Components\BaseControl 
{
    /** @var UserFacade @inject */
	public $userFacade;
    
	/** @var ArrayCollection */
	private $companies;
	
	
	/**
	 * @inheritdoc
	 */
	public function render() {
		$this->companies = ($this->companies)  ?  $this->companies  :  new ArrayCollection();
		$this->template->companies = $this->companies;
        $this->template->addFilter('canAccess', $this->userFacade->canAccess);
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

