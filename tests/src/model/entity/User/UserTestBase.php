<?php

namespace Test\Model\Entity;

use App\Model\Entity\Facebook;
use App\Model\Entity\PageConfigSettings;
use App\Model\Entity\PageDesignSettings;
use App\Model\Entity\Role;
use App\Model\Entity\Twitter;
use App\Model\Entity\User;
use Kdyby\Doctrine\EntityDao;
use Nette\DI\Container;
use Test\DbTestCase;

abstract class UserTestBase extends DbTestCase
{

	const MAIL = 'jack@sg1.sg.gov';
	const PASSWORD = 'ThorIsMyFri3nd';

	/** @var User */
	protected $user;

	/** @var EntityDao */
	protected $roleDao;

	/** @var EntityDao */
	protected $userDao;

	public function __construct(Container $container)
	{
		parent::__construct($container);

		$this->userDao = $this->em->getDao(User::getClassName());
		$this->roleDao = $this->em->getDao(Role::getClassName());
	}

	public function setUp()
	{
		parent::setUp();
		$this->updateSchema();
		$this->user = new User(self::MAIL);
	}

	public function tearDown()
	{
		$this->dropSchema();
		parent::tearDown();
	}

	protected function saveUser($safePersist = FALSE)
	{
		if ($safePersist) {
			$this->em->safePersist($this->user);
			$this->em->flush();
		} else {
			$this->userDao->save($this->user);
		}
		$this->reloadUser();
		return $this;
	}

	protected function reloadUser()
	{
		$this->em->detach($this->user);
		$this->user = $this->userDao->find($this->user->id);
		return $this;
	}

	protected function getClasses()
	{
		return [
				$this->em->getClassMetadata(User::getClassName()),
				$this->em->getClassMetadata(Role::getClassName()),
				$this->em->getClassMetadata(PageConfigSettings::getClassName()),
				$this->em->getClassMetadata(PageDesignSettings::getClassName()),
				$this->em->getClassMetadata(Facebook::getClassName()),
				$this->em->getClassMetadata(Twitter::getClassName()),
		];
	}

}
