<?php

namespace Test\Model\Entity;

use Nette\DI\Container;
use Kdyby\Doctrine\EntityDao;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use Test\ParentTestCase;
use Tester\Environment;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Skill entity
 *
 * @testCase
 */
class SkillTest extends ParentTestCase
{
	
	const S_NAME = 'foo';
	
	/** @var EntityDao */
	protected $skillDao;
	
	/** @var EntityDao */
	protected $skillCategoryDao;
	
	/** @var Skill */
	protected $skill;
	
	public function __construct(Container $container)
	{
		parent::__construct($container);
		
		$this->skillDao = $this->em->getDao(Skill::getClassName());
		$this->skillCategoryDao = $this->em->getDao(SkillCategory::getClassName());
	}
	
	public function setUp()
	{
		$this->skill = new Skill;
	}
	
	public function tearDown()
	{
		unset($this->skill);
	}
	
	public function testSetAndGet()
	{
		$this->skill->name = self::S_NAME;
		Assert::same(self::S_NAME, $this->skill->name);
		
		$category = new SkillCategory;
		$this->skill->category = $category;
		Assert::same($category, $this->skill->category);
	}
	
}

$test = new SkillTest($container);
$test->run();