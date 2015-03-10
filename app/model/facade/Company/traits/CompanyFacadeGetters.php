<?php

namespace App\Model\Facade\Traits;

trait CompanyFacadeGetters
{

	public function getCompaniesNames()
	{
		return $this->companyDao->findPairs('name');
	}

	public function getRolesNames()
	{
		return $this->companyRoleDao->findPairs('name');
	}

}
