<?php

namespace App\Model\Facade;

use App\Model\Entity\Company;
use App\Model\Entity\CompanyPermission;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\User;
use App\Model\Facade\Traits\CompanyFacadeCheckers;
use App\Model\Facade\Traits\CompanyFacadeFinders;
use App\Model\Facade\Traits\CompanyFacadeGetters;
use App\Model\Repository\CompanyPermissionRepository;
use App\Model\Repository\UserRepository;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class CompanyFacade extends Object
{

	use CompanyFacadeFinders;
	use CompanyFacadeCheckers;
	use CompanyFacadeGetters;

	/** @var EntityManager @inject */
	public $em;

	/** @var CompanyPermissionRepository */
	private $companyPermissionRepo;

	/** @var UserRepository */
	private $userRepo;

	/** @var EntityDao */
	private $companyDao;

	/** @var EntityDao */
	private $companyRoleDao;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->companyPermissionRepo = $this->em->getRepository(CompanyPermission::getClassName());
		$this->userRepo = $this->em->getRepository(User::getClassName());
		$this->companyDao = $this->em->getDao(Company::getClassName());
		$this->companyRoleDao = $this->em->getDao(CompanyRole::getClassName());
	}

	/**
	 * Create company with admin permission
	 * @param Company|string $companyOrCompanyName
	 * @param User $user
	 * @return Company
	 */
	public function create($companyOrCompanyName, User $user)
	{
		if ($companyOrCompanyName instanceof Company) {
			$company = $companyOrCompanyName;
		} else {
			$company = new Company($companyOrCompanyName);
		}
		$this->em->persist($company);
		$this->em->flush();

		$this->createPermission($company, $user, CompanyRole::ADMIN);

		return $company;
	}

	public function createPermission(Company $company, User $user, $role = CompanyRole::ADMIN)
	{
		$adminAccess = new CompanyPermission();
		$adminAccess->user = $user;
		$adminAccess->company = $company;
		$adminAccess->addRole($this->findRoleByName($role));
		$this->companyPermissionRepo->save($adminAccess);

		return $adminAccess;
	}

	/**
	 * Create role if isn't exists
	 * @param string $name
	 * @return CompanyRole|NULL
	 */
	public function createRole($name)
	{
		if (!$this->findRoleByName($name)) {
			$role = new CompanyRole($name);
			$this->em->persist($role);
			$this->em->flush();
			return $role;
		}
		return NULL;
	}

	/**
	 * Add permission for inserted company and user with inserted roles
	 * @param string|Company $company Company or CompanyId
	 * @param string|User $user User or UserId
	 * @param array $roles
	 * @param bool $clearRoles
	 * @return CompanyPermission|NULL
	 */
	public function addPermission($company, $user, array $roles, $clearRoles = TRUE)
	{
		if (!count($roles)) {
			return NULL;
		}
		$findedCompany = $this->find($company);
		$userId = $user instanceof User ? $user->id : (string)$user;
		$findedUser = $this->userRepo->find($userId);

		$permission = $this->findPermission($findedCompany, $findedUser);
		if (!$permission) {
			$permission = new CompanyPermission();
		} else if ($clearRoles) {
			$permission->clearRoles();
		}
		$permission->user = $findedUser;
		$permission->company = $findedCompany;
		foreach ($roles as $role) {
			$roleName = $role;
			if ($role instanceof CompanyRole) {
				$roleName = $role->name;
			}
			$permission->addRole($this->findRoleByName($roleName));
		}

		return $this->companyPermissionRepo->save($permission);
	}

	/**
	 * Delete with all connections
	 * @param Company $company
	 */
	public function delete(Company $company)
	{
		$this->clearPermissions($company);
		$this->clearJobs($company);
		$this->em->remove($company);
		$this->em->flush();
		return $company;
	}

	public function clearPermissions(Company $company)
	{
		foreach ($this->findPermissions($company) as $permission) {
			$this->em->remove($permission);
		}
		$this->em->flush();
		return TRUE;
	}

	public function clearJobs(Company $company)
	{
		foreach ($company->jobs as $job) {
			$this->em->remove($job);
		}
		$this->em->flush();
		return true;
	}
}
