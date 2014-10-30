<?php

namespace Test;

use App\Model\Entity\Auth;
use App\Model\Entity\Registration;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use App\Model\Entity\UserSettings;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Nette\DI\Container;
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
		return [
			$this->em->getClassMetadata(User::getClassName()),
			$this->em->getClassMetadata(UserSettings::getClassName()),
			$this->em->getClassMetadata(Role::getClassName()),
			$this->em->getClassMetadata(Auth::getClassName()),
			$this->em->getClassMetadata(Registration::getClassName()),
			$this->em->getClassMetadata(Skill::getClassName()),
			$this->em->getClassMetadata(SkillCategory::getClassName()),
		];
	}

}