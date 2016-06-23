<?php

namespace App\Model\Facade\Traits;

use App\Model\Entity\Facebook;
use App\Model\Entity\Registration;
use App\Model\Entity\Role;
use App\Model\Entity\Twitter;
use App\Model\Entity\User;
use Nette\Utils\DateTime;
use Nette\Utils\Random;

trait UserFacadeCreates
{

	/**
	 * @param string $mail
	 * @param string $password
	 * @param Role $role
	 * @return User
	 */
	public function create($mail, $password, Role $role)
	{
		if ($this->isUnique($mail)) {
			$user = new User();
			$user->setMail($mail)
				->setPassword($password)
				->addRole($role);
            $this->setVerification($user);
			return $this->userRepo->save($user);
		}
		return NULL;
	}

}
