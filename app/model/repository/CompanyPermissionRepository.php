<?php

namespace App\Model\Repository;

use App\Model\Entity\Company;
use Kdyby\Doctrine\EntityRepository;

class CompanyPermissionRepository extends EntityRepository
{

	public function findByCompanyAndRoleId(Company $findedCompany, $roleId, $onlyIds = FALSE)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$selection = $onlyIds ? 'IDENTITY(p.user)' : 'p';
		$permissions = $qb->select($selection)
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
