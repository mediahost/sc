<?php

namespace App\Security;

use App\Model\Entity\CompanyRole;
use Nette\Security;

class CompanyPermission extends Security\Permission
{

	public function __construct()
	{
		$this->addRole(CompanyRole::MESSENGER);
		$this->addRole(CompanyRole::JOBBER);
		$this->addRole(CompanyRole::EDITOR);
		$this->addRole(CompanyRole::MANAGER);
		$this->addRole(CompanyRole::ADMIN);

		$this->addResource('sidebar-menu');
		$this->addResource('info');
		$this->addResource('users');
		$this->addResource('jobs');
		$this->addResource('messages');


		$this->deny(CompanyRole::MESSENGER);
		$this->allow(CompanyRole::MESSENGER, 'messages');

		$this->deny(CompanyRole::JOBBER);
		$this->allow(CompanyRole::JOBBER, 'jobs');

		$this->deny(CompanyRole::EDITOR);
		$this->allow(CompanyRole::EDITOR, 'sidebar-menu');
		$this->allow(CompanyRole::EDITOR, 'info');

		$this->deny(CompanyRole::MANAGER);
		$this->allow(CompanyRole::EDITOR, 'sidebar-menu');
		$this->allow(CompanyRole::MANAGER, 'info', ['view']);
		$this->allow(CompanyRole::MANAGER, ['users', 'jobs']);

		$this->allow(CompanyRole::ADMIN);
	}

}
