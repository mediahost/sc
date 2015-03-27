<?php

namespace Test\Model\Facade;

use App\Model\Entity\Company;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\User;
use App\Model\Repository\UserRepository;
use Kdyby\Doctrine\EntityDao;
use Nette\DI\Container;

abstract class CompanyFacade extends BaseFacade
{

	const ID_NEW = 3;
	const MAIL = 'company.mail@domain.com';
	const PASSWORD = 'password123456';
	const EXPIRED_TOKEN = 'expiredToken';
	const VALID_TOKEN = 'validToken';
	const TWITTER_ID = 'tw123456789';
	const FACEBOOK_ID = 'fb123456789';

	/** @var UserRepository */
	protected $userRepo;

	/** @var EntityDao */
	protected $companyDao;

	/** @var User */
	protected $user;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->userRepo = $this->em->getRepository(User::getClassName());
		$this->companyDao = $this->em->getDao(Company::getClassName());
		$this->companyRoleDao = $this->em->getDao(CompanyRole::getClassName());
	}

	protected function setUp()
	{
		parent::setUp();
		$this->importDbDataFromFile(__DIR__ . '/sql/users_after_install_and_add_companies.sql');
	}

}
