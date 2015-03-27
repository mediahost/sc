<?php

namespace App\Model\Facade\Traits;

use App\Model\Entity\Company;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\User;

trait CompanyFacadeFinders
{

	public function find($companyIdOrEntity)
	{
		$id = $companyIdOrEntity instanceof Company ? $companyIdOrEntity->id : (string) $companyIdOrEntity;
		return $this->companyDao->find($id);
	}

	public function findByCompanyId($companyId)
	{
		return $this->companyDao->findOneBy(['companyId' => $companyId]);
	}

	public function findByName($name)
	{
		return $this->companyDao->findOneBy(['name' => $name]);
	}

	public function findPermission(Company $company, User $user)
	{
		return $this->companyPermissionRepo->findOneBy([
				'company' => $company,
				'user'    => $user,
		]);
	}

	public function findPermissions($company = NULL, $user = NULL)
	{
		if ($company instanceof Company) {
			return $this->companyPermissionRepo->findBy(['company' => $company]);
		}
		if ($user instanceof User) {
			return $this->companyPermissionRepo->findBy(['user' => $user]);
		}
		return [];
	}

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
		$permissions = $this->companyPermissionRepo->findBy([
				'company' => $this->find($company),
		]);
		$users = [];
		foreach ($permissions as $permission) {
			$users[] = $onlyIds ? $permission->user->id : $permission->user;
		}
		return $users;
	}

	public function findUsersByCompanyAndRole($companyIdOrEntity, $roleOrRoleName, $onlyIds = FALSE)
	{
		$users = [];

		$company = $this->find($companyIdOrEntity);
		$role = $roleOrRoleName instanceof CompanyRole ? $roleOrRoleName : $this->findRoleByName($roleOrRoleName);

		if ($company && $role) {
			$permissions = $this->companyPermissionRepo->findByCompanyAndRoleId($company, $role->id, $onlyIds);
			foreach ($permissions as $permission) {
				$users[] = $onlyIds ? current($permission) : $permission->user;
			}
		}
		return $users;
	}

}
