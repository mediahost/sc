<?php

namespace Test\Model\Entity;

use App\Model\Entity\Company;
use Kdyby\Doctrine\EntityDao;
use Nette\DI\Container;
use Test\DbTestCase;

abstract class CompanyTestBase extends DbTestCase
{

	const NAME = 'Testovací společnost';
	const COMPANY_ID = '123-345-567 99';
	const ADDRESS = 'Silniční 123, Město nad Řekou';

	/** @var Company */
	protected $company;

	/** @var EntityDao */
	protected $companyDao;

	public function __construct(Container $container)
	{
		parent::__construct($container);

		$this->companyDao = $this->em->getDao(Company::getClassName());
	}

	public function setUp()
	{
		$this->updateSchema();
		$this->company = new Company(self::NAME);
	}

	public function tearDown()
	{
		unset($this->company);
		$this->dropSchema();
	}

	protected function saveCompany($safePersist = FALSE)
	{
		if ($safePersist) {
			$this->em->safePersist($this->user);
			$this->em->flush();
		} else {
			$this->companyDao->save($this->company);
		}
		$this->reloadCompany();
		return $this;
	}

	protected function reloadCompany()
	{
		$this->em->detach($this->company);
		$this->company = $this->companyDao->find($this->company->id);
		return $this;
	}

	protected function getClasses()
	{
		return [
				$this->em->getClassMetadata(Company::getClassName()),
		];
	}

}
