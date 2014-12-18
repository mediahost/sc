<?php

namespace App\Security;

use App\Model\Entity\CompanyRole;
use Nette\Security;

class CompanyPermission extends Security\Permission
{

	public function __construct()
	{
		$this->addRole(CompanyRole::EDITOR);
		$this->addRole(CompanyRole::MANAGER);
		$this->addRole(CompanyRole::ADMIN, [CompanyRole::EDITOR, CompanyRole::MANAGER]);

		$this->addResource('info');
		$this->addResource('users');

		$this->deny('editor');
		$this->deny('manager');

		$this->allow('manager', 'info');
		$this->allow('admin');
	}

}
