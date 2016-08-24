<?php

namespace App\Model\Facade\Traits;

use App\Model\Entity\User;
use Nette\Utils\Random;

trait UserFacadeRecovery
{

	/**
	 * Sets recovery token and expiration datetime to User.
	 * @param User $user
	 * @return self
	 */
	public function setRecovery(User &$user)
	{
		$token = Random::generate(32);
		$user->setRecovery($token, 'now + ' . $this->settings->expiration->recovery);
		return $this;
	}

	/**
	 * @param User $user
	 * @param string $password
	 * @return self
	 */
	public function recoveryPassword(User &$user, $password)
	{
		$user->password = $password;
		$user->removeRecovery();
		return $this;
	}

	/**
	 * Create registration
	 * @param User $user
	 * @return Registration
	 */
	public function setVerification(User $user)
	{
		$token = Random::generate(32);
		$user->setVerification($token, 'now + ' . $this->settings->expiration->verification);

		return $user;
	}

}
