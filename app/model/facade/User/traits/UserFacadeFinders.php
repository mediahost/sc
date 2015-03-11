<?php

namespace App\Model\Facade\Traits;

use App\Model\Entity\Registration;
use Nette\Utils\DateTime;

trait UserFacadeFinders
{

	public function findByMail($mail)
	{
		return $this->userDao->findOneBy(['mail' => $mail]);
	}

	public function findByFacebookId($id)
	{
		return $this->userDao->findOneBy(['facebook.id' => $id]);
	}

	public function findByTwitterId($id)
	{
		return $this->userDao->findOneBy(['twitter.id' => $id]);
	}

	/**
	 * Find only valid entities
	 * Expired sign up request is deleted
	 * @param string $token
	 * @return Registration
	 */
	public function findByVerificationToken($token)
	{
		$registration = $this->registrationDao->findOneBy(['verificationToken' => $token]);

		if ($registration) {
			if ($registration->verificationExpiration > new DateTime()) {
				return $registration;
			} else {
				$this->registrationDao->delete($registration);
			}
		}

		return NULL;
	}

	public function findByRecoveryToken($token)
	{
		if (!empty($token)) {
			$user = $this->userDao->findOneBy([
					'recoveryToken' => $token
			]);

			if ($user) {
				if ($user->recoveryExpiration > new DateTime()) {
					return $user;
				} else {
					$user->removeRecovery();
					$this->userDao->save($user);
				}
			}
		}

		return NULL;
	}

}
