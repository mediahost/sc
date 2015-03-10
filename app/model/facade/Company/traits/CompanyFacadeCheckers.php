<?php

namespace App\Model\Facade\Traits;

trait CompanyFacadeCheckers
{

	public function isUniqueId($companyId, $id = NULL)
	{
		$finded = $this->findByCompanyId($companyId);
		if ($finded) {
			return $finded->id === $id;
		}
		return TRUE;
	}

	public function isUniqueName($name, $id = NULL)
	{
		$finded = $this->findByName($name);
		if ($finded) {
			return $finded->id === $id;
		}
		return TRUE;
	}

}
