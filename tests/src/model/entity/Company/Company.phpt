<?php

namespace Test\Model\Entity;

use Kdyby\Doctrine\MemberAccessException;
use Tester\Assert;

$container = require __DIR__ . '/../../../bootstrap.php';

/**
 * TEST: Company entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyTest extends CompanyTestBase
{

	public function testSetCompany()
	{
		$this->company->companyId = self::COMPANY_ID;
		$this->company->address = self::ADDRESS;
		$this->company->mail = self::MAIL;

		Assert::exception(function () {
			$this->company->id = 123;
		}, MemberAccessException::class);

		$this->saveCompany();

		Assert::same(self::NAME, $this->company->name);
		Assert::same(self::COMPANY_ID, $this->company->companyId);
		Assert::same(self::ADDRESS, $this->company->address);

		Assert::same(self::NAME, (string) $this->company);
	}

	public function testIsNew()
	{
		$this->company->mail = self::MAIL;
		Assert::TRUE($this->company->isNew());
		$this->saveCompany();
		Assert::FALSE($this->company->isNew());
	}

}

$test = new CompanyTest($container);
$test->run();
