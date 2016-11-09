<?php

namespace App\Model\Facade\Traits;

use App\Model\Entity\Registration;
use App\Model\Entity\Role;
use App\Model\Entity\User;

trait UserFacadeCreates
{

	public function create($mail, $password, Role $role, $createSender = TRUE)
	{
		if ($this->isUnique($mail)) {
			$user = new User();
			$user->setMail($mail)
				->setPassword($password)
				->addRole($role);
			$this->setVerification($user);
			$this->userRepo->save($user);
			if ($createSender) {
				$this->communicationFacade->createSender($user);
			}

			return $user;
		}

		return NULL;
	}

}
