<?php

namespace App\Model\Facade;

use App\Model\Entity\Company;
use App\Model\Entity\CompanyPermission;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\User;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

/**
 * CompanyFacade
 */
class CompanyFacade extends Object
{

	/** @var EntityManager @inject */
	public $em;

	/** @var EntityDao */
	private $companyDao;

	/** @var EntityDao */
	private $companyRoleDao;

	/** @var EntityDao */
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

	// <editor-fold defaultstate="expanded" desc="create & add">

	/**
	 * Create role if isn't exists
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

		$this->companyPermissionDao->save($permission);
		return $permission;
	}

	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="getters">

	/**
	 * Get all companies
	 * @return array
	 */
	public function getCompanies()
	{
		return $this->companyDao->findPairs([], 'name', [], 'id');
	}

	/**
	 * Get all roles
	 * @return array
	 */
	public function getRoles()
	{
		return $this->companyRoleDao->findPairs([], 'name', [], 'id');
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="finders">

	/**
	 * Find company by entity or ID
	 * @param type $company
	 * @return Company|NULL
	 */
	public function find($company)
	{
		$companyId = $company instanceof Company ? $company->id : (string) $company;
		return $this->companyDao->find($companyId);
	}

	/**
	 * Find company by company id.
	 * @param type $companyId
	 * @return Company
	 */
	public function findByCompanyId($companyId)
	{
		return $this->companyDao->findOneBy(['companyId' => $companyId]);
	}

	/**
	 * Find company by name.
	 * @param type $name
	 * @return Company
	 */
	public function findByName($name)
	{
		return $this->companyDao->findOneBy(['name' => $name]);
	}

	/**
	 * Find company permission by company and user
	 * @param Company $company
	 * @param User $user
	 * @return CompanyPermission|NULL
	 */
	public function findPermission(Company $company, User $user)
	{
		return $this->companyPermissionDao->findOneBy([
					'company' => $company,
					'user' => $user,
		]);
	}

	/**
	 * Find company permissions by company or user
	 * @param Company $company
	 * @param User $user
	 * @return array
	 */
	public function findPermissions($company = NULL, $user = NULL)
	{
		if ($company instanceof Company) {
			return $this->companyPermissionDao->findBy(['company' => $company]);
		}
		if ($user instanceof User) {
			return $this->companyPermissionDao->findBy(['user' => $user]);
		}
		return [];
	}

	/**
	 * Find company role by name
	 * @param string $name
	 * @return CompanyRole|NULL
	 */
	public function findRoleByName($name)
	{
		return $this->companyRoleDao->findOneBy(['name' => $name]);
	}

	/**
	 * Find users (or its ids) by company
	 * @param string|Company $company
	 * @param bool $onlyIds Return only user ids
	 * @return array
	 */
	public function findUsersByCompany($company, $onlyIds = FALSE)
	{
		$permissions = $this->companyPermissionDao->findBy([
			'company' => $this->find($company),
		]);
		$users = [];
		foreach ($permissions as $permission) {
			$users[] = $onlyIds ? $permission->user->id : $permission->user;
		}
		return $users;
	}

	/**
	 * Find users by company and role
	 * @param string|Company $company
	 * @param string|Role $role
	 * @param bool $onlyIds Return only user ids
	 * @return array
	 */
	public function findUsersByCompanyAndRole($company, $role, $onlyIds = FALSE)
	{
		$findedCompany = $this->find($company);
		$findedRole = $role instanceof CompanyRole ? $role : $this->findRoleByName($role);

		$qb = $this->em->createQueryBuilder();
		$selection = $onlyIds ? 'IDENTITY(p.user)' : 'p';
		$permissions = $qb->select($selection)
				->from(CompanyPermission::getClassName(), 'p')
				->innerJoin('p.roles', 'r')
				->where('p.company = :company')
				->andWhere('r.id = :roleid')
				->setParameter('company', $findedCompany)
				->setParameter('roleid', $findedRole->id)
				->getQuery()
				->getResult();
		$users = [];
		foreach ($permissions as $permission) {
			$users[] = $onlyIds ? current($permission) : $permission->user;
		}
		return $users;
	}

	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="checkers">

	/**
	 * Check if company ID is unique.
	 * @param type $companyId
	 * @param type $id row with this id is unique
	 * @return bool
	 */
	public function isUniqueId($companyId, $id = NULL)
	{
		$finded = $this->findByCompanyId($companyId);
		if ($finded) {
			return $finded->id === $id;
		}
		return TRUE;
	}

	/**
	 * Check if company name is unique.
	 * @param type $name
	 * @param type $id row with this id is unique
	 * @return bool
	 */
	public function isUniqueName($name, $id = NULL)
	{
		$finded = $this->findByName($name);
		if ($finded) {
			return $finded->id === $id;
		}
		return TRUE;
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="delete">

	/**
	 * Delete company and all permission for this company
	 * @param Company $company
	 * @return bool
	 */
	public function delete(Company $company)
	{
		$this->clearPermissions($company);
		return $this->companyDao->delete($company);
	}
	
	/**
	 * Delete all permission for this company
	 * @param Company $company
	 * @return bool
	 */
	public function clearPermissions(Company $company)
	{
		foreach ($this->findPermissions($company) as $permission) {
			$this->companyPermissionDao->delete($permission);
		}
		return TRUE;
	}

	// </editor-fold>
}
