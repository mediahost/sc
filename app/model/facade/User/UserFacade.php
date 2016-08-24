<?php

namespace App\Model\Facade;

use App\Extensions\Settings\SettingsStorage;
use App\Model\Entity\CompanyPermission;
use App\Model\Entity\Registration;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\Traits\UserFacadeAccess;
use App\Model\Facade\Traits\UserFacadeCreates;
use App\Model\Facade\Traits\UserFacadeDelete;
use App\Model\Facade\Traits\UserFacadeFinders;
use App\Model\Facade\Traits\UserFacadeGetters;
use App\Model\Facade\Traits\UserFacadeRecovery;
use App\Model\Facade\Traits\UserFacadeSetters;
use App\Model\Repository\CompanyPermissionRepository;
use App\Model\Repository\UserRepository;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class UserFacade extends Object
{
    use UserFacadeAccess;
	use UserFacadeCreates;
	use UserFacadeDelete;
	use UserFacadeFinders;
	use UserFacadeGetters;
	use UserFacadeSetters;
	use UserFacadeRecovery;

	/** @var EntityManager @inject */
	public $em;

	/** @var SettingsStorage @inject */
	public $settings;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var UserRepository */
	private $userRepo;

	/** @var CompanyPermissionRepository */
	private $companyPermissionRepo;

	/** @var EntityDao */
	private $roleDao;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->userRepo = $this->em->getRepository(User::getClassName());
		$this->companyPermissionRepo = $this->em->getRepository(CompanyPermission::getClassName());
		$this->roleDao = $this->em->getDao(Role::getClassName());
	}

	public function isUnique($mail)
	{
		return $this->findByMail($mail) === NULL;
	}
}
