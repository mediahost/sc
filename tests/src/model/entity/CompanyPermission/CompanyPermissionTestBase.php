<?php

namespace Test\Model\Entity;

use App\Model\Entity\Company;
use App\Model\Entity\CompanyPermission;
use App\Model\Entity\User;
use Test\BaseTestCase;

abstract class CompanyPermissionTestBase extends BaseTestCase
{

	const USER_MAIL = 'user@domain.com';
	const COMPANY_NAME = 'company';

	/** @var  CompanyPermission */
	protected $companyPermission;

	protected function setUp()
	{
		$this->setCompanyPermission();
		parent::setUp();
	}

	private function setCompanyPermission()
	{
		$user = new User('user@domain.com');
		$company = new Company('company');

		$this->companyPermission = new CompanyPermission;
		$this->companyPermission->user = $user;
		$this->companyPermission->company = $company;

		return $this;
	}

}
