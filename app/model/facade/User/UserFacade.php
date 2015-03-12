<?php

namespace App\Model\Facade;

use App\Extensions\Settings\Model\Service\ExpirationService;
use App\Model\Entity\CompanyPermission;
use App\Model\Entity\PageConfigSettings;
use App\Model\Entity\PageDesignSettings;
use App\Model\Entity\Registration;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\Traits\UserFacadeCreates;
use App\Model\Facade\Traits\UserFacadeDelete;
use App\Model\Facade\Traits\UserFacadeFinders;
use App\Model\Facade\Traits\UserFacadeGetters;
use App\Model\Facade\Traits\UserFacadeRecovery;
use App\Model\Facade\Traits\UserFacadeSetters;
use App\Model\Repository\CompanyPermissionRepository;
use App\Model\Repository\RegistrationRepository;
use App\Model\Repository\UserRepository;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class UserFacade extends Object
{

	use UserFacadeCreates;
	use UserFacadeDelete;
	use UserFacadeFinders;
	use UserFacadeGetters;
	use UserFacadeSetters;
	use UserFacadeRecovery;

	/** @var EntityManager @inject */
	public $em;

	/** @var ExpirationService @inject */
	public $expirationService;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var UserRepository */
	private $userDao;

	/** @var EntityDao */
	private $roleDao;

	/** @var RegistrationRepository */
	private $registrationDao;

	/** @var EntityDao */
	private $configSettingsDao;

	/** @var EntityDao */
	private $designSettingsDao;

	/** @var CompanyPermissionRepository */
	private $companyPermissionDao;

	public function __construct(EntityManager $em, ExpirationService $expiration)
	{
		$this->expirationService = $expiration;
		$this->em = $em;
		$this->userDao = $this->em->getDao(User::getClassName());
		$this->roleDao = $this->em->getDao(Role::getClassName());
		$this->registrationDao = $this->em->getDao(Registration::getClassName());
		$this->configSettingsDao = $this->em->getDao(PageConfigSettings::getClassName());
		$this->designSettingsDao = $this->em->getDao(PageDesignSettings::getClassName());
		$this->companyPermissionDao = $this->em->getDao(CompanyPermission::getClassName());
	}

	public function isUnique($mail)
	{
		return $this->findByMail($mail) === NULL;
	}

}