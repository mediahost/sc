<?php

namespace Test;

use Doctrine\ORM\Tools\SchemaTool;
use Kdyby\Doctrine\Connection;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\Helpers;
use Kdyby\TesterExtras\Bootstrap;
use Tester\Environment;

abstract class DbTestCase extends BaseTestCase
{

	/** @var EntityManager @inject */
	public $em;

	/** @var SchemaTool */
	protected $schemaTool;

	protected function updateSchema()
	{
		if (getenv(Environment::RUNNER)) {
			Environment::lock('db', LOCK_DIR);
		}
		if (!$this->schemaTool instanceof SchemaTool) {
			$this->schemaTool = new SchemaTool($this->em);
		}
		Bootstrap::setupDoctrineDatabase($this->getContainer(), [], 'sc');
		$this->schemaTool->updateSchema($this->getClasses());
	}

	protected function importDbDataFromFile($file)
	{
		$db = $this->getContainer()->getByType(Connection::class);
		Helpers::loadFromFile($db, $file);
	}

	protected function dropSchema()
	{
		if (!$this->schemaTool instanceof SchemaTool) {
			$this->schemaTool = new SchemaTool($this->em);
		}
		$this->schemaTool->dropSchema($this->getClasses());
		$this->em->clear();
	}

	protected function getClasses()
	{
		return $this->em->getMetadataFactory()->getAllMetadata();
	}

}
