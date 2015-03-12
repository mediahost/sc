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

	/** @var EntityDao */
	private $companyDao;

	/** @var EntityDao */
	private $companyRoleDao;

	/** @var CompanyPermissionRepository */
	private $companyPermissionDao;

	/** @var EntityDao */
	private $userDao;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->companyDao = $this->em->getDao(Company::getClassName());
		$this->companyRoleDao = $this->em->getDao(CompanyRole::getClassName());
		$this->companyPermissionDao = $this->em->getDao(CompanyPermission::getClassName());
		$this->userDao = $this->em->getDao(User::getClassName());
	}

	/**
	 * TODO: test it
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
		$createdCompany = $this->companyDao->save($company);

		$adminAccess = new CompanyPermission();
		$adminAccess->user = $user;
		$adminAccess->company = $createdCompany;
		$adminAccess->addRole($this->findRoleByName(CompanyRole::ADMIN));
		$this->companyPermissionDao->save($adminAccess);

		return $createdCompany;
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
			return $this->companyRoleDao->save($role);
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
		$userId = $user instanceof User ? $user->id : (string) $user;
		$findedUser = $this->userDao->find($userId);

		$permission = $this->findPermission($findedCompany, $findedUser);
		if (!$permission) {
			$permission = new CompanyPermission;
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

		return $this->companyPermissionDao->save($permission);
	}

	/**
	 * Delete with all connections
	 * @param Company $company
	 */
	public function delete(Company $company)
	{
		$this->clearPermissions($company);
		return $this->companyDao->delete($company);
	}

	public function clearPermissions(Company $company)
	{
		foreach ($this->findPermissions($company) as $permission) {
			$this->companyPermissionDao->delete($permission);
		}
		return TRUE;
	}

}
