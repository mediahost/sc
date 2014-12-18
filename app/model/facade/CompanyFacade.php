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
 * TODO: TEST IT!!!
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
	 * TODO: TEST IT!!!
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
	 * Find company role by name
	 * @param string $name
	 * @return CompanyRole|NULL
	 */
	public function findRoleByName($name)
	{
		return $this->companyRoleDao->findOneBy(['name' => $name]);
	}

	/**
	 * Find company role by name
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
	public function isUnique($companyId, $id = NULL)
	{
		$finded = $this->findByCompanyId($companyId);
		if ($finded && $id) {
			return $finded->id === $id;
		}
		return TRUE;
	}

	// </editor-fold>
}
