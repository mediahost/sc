<?php

namespace App\Security;

use App\Model\Entity\CompanyRole;
use Nette\Security;

class CompanyPermission extends Security\Permission
{

	public function __construct()
	{
		$this->addRole(CompanyRole::MESSENGER);
		$this->addRole(CompanyRole::EDITOR);
		$this->addRole(CompanyRole::MANAGER);
		$this->addRole(CompanyRole::ADMIN, [CompanyRole::EDITOR, CompanyRole::MANAGER]);

		$this->addResource('info');
		$this->addResource('users');
		$this->addResource('jobs');
		$this->addResource('messages');

		$this->deny('editor');
		$this->deny('manager');

		$this->allow(CompanyRole::MESSENGER, 'messages');
		$this->allow(CompanyRole::MESSENGER, 'info', ['view']);
		$this->allow('editor', 'info');
		$this->allow('manager', 'info', ['view']);
		$this->allow('manager', ['users', 'jobs']);
		$this->allow('admin');
	}

}
