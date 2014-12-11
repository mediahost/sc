<?php

namespace Test;

use Doctrine\ORM\Tools\SchemaTool;
use Kdyby\Doctrine\EntityManager;
use Nette\DI\Container;
use Tester\Environment;
use Tester\TestCase;

/**
 * ParentTestCase
 *
 * @author Petr Poupě
 */
abstract class ParentTestCase extends TestCase
{

	/** @var Container */
	protected $container;

	/** @var EntityManager @inject */
	public $em;

	/** @var SchemaTool */
	protected $schemaTool;

	function __construct(Container $container)
	{
		$this->container = $container;
		$this->container->callInjects($this);
	}
	
	protected function updateSchema()
	{
		Environment::lock('db', LOCK_DIR);
		if (!$this->schemaTool instanceof SchemaTool) {
			$this->schemaTool = new SchemaTool($this->em);
		}
		$this->schemaTool->updateSchema($this->getClasses());
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
