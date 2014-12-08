<?php

namespace Test;

use App\Model\Entity\Candidate;
use App\Model\Entity\Company;
use App\Model\Entity\Facebook;
use App\Model\Entity\OAuth;
use App\Model\Entity\PageConfigSettings;
use App\Model\Entity\PageDesignSettings;
use App\Model\Entity\Registration;
use App\Model\Entity\Role;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use App\Model\Entity\Twitter;
use App\Model\Entity\User;
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
	}

	protected function getClasses()
	{
		return $this->em->getMetadataFactory()->getAllMetadata();
	}

}
