<?php

namespace App\Model\Repository;

use App\Model\Entity\Company;

class CompanyPermissionRepository extends BaseRepository
{

	public function findByCompanyAndRoleId(Company $findedCompany, $roleId, $onlyIds = FALSE)
	{
		$selection = $onlyIds ? 'IDENTITY(p.user)' : 'p';
		$permissions = $this->createQueryBuilder()
				->select($selection)
				->from($this->getEntityName(), 'p')
				->innerJoin('p.roles', 'r')
				->where('p.company = :company')
				->andWhere('r.id = :roleid')
				->setParameter('company', $findedCompany)
				->setParameter('roleid', $roleId)
				->getQuery()
				->getResult();

		return $permissions;
	}

}
