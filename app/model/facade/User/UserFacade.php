<?php

namespace App\Model\Facade;

use App\Extensions\Settings\SettingsStorage;
use App\Model\Entity\Action;
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
use Doctrine\ORM\EntityRepository;
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

	/** @var CommunicationFacade @inject */
	public $communicationFacade;

	/** @var CandidateFacade @inject */
	public $candidateFacade;

	/** @var UserRepository */
	private $userRepo;

	/** @var CompanyPermissionRepository */
	private $companyPermissionRepo;

	/** @var EntityRepository @inject */
	private $actionRepo;

	/** @var EntityDao */
	private $roleDao;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->userRepo = $this->em->getRepository(User::getClassName());
		$this->companyPermissionRepo = $this->em->getRepository(CompanyPermission::getClassName());
		$this->actionRepo = $this->em->getRepository(Action::getClassName());
		$this->roleDao = $this->em->getDao(Role::getClassName());
	}

	public function isUnique($mail, $tolerate = NULL, $tolerateUnregistered = FALSE)
	{
		$finded = $this->userRepo->findByMail($mail);
		if ($tolerateUnregistered && count($finded) === 1) {
			$findedEntity = current($finded);
			if ($findedEntity->isUnregistered()) {
				return TRUE;
			}
		}
		if ($tolerate && count($finded) === 1) {
			$findedEntity = current($finded);
			return $findedEntity->mail === $tolerate;
		} else {
			return count($finded) === 0;
		}
	}
}
