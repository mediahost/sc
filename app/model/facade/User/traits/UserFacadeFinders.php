<?php

namespace App\Model\Facade\Traits;

use App\Model\Entity\Registration;
use App\Model\Entity\User;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

trait UserFacadeFinders
{

	public function findByMail($mail)
	{
		return $this->userRepo->findOneBy(['mail' => Strings::lower($mail)]);
	}

	public function findByFacebookId($id)
	{
		return $this->userRepo->findOneBy(['facebook.id' => $id]);
	}

	public function findByTwitterId($id)
	{
		return $this->userRepo->findOneBy(['twitter.id' => $id]);
	}

	public function findByLinkedinId($id)
	{
		return $this->userRepo->findOneBy(['linkedin.id' => $id]);
	}

	public function findById($id)
	{
		return $this->userRepo->findOneBy(['id' => $id]);
	}

	/**
	 * Find only valid entities
	 * Expired sign up request is deleted
	 * @param string $token
	 * @return Registration
	 */
	public function findByVerificationToken($token)
	{
		$user = $this->userRepo->findOneBy(['verificationToken' => $token]);

		if ($user && $user->verificationExpiration > new DateTime()) {
			return $user;
		}

		return NULL;
	}

	public function findByRecoveryToken($token)
	{
		if (!empty($token)) {
			$user = $this->userRepo->findOneBy([
				'recoveryToken' => $token
			]);

			if ($user) {
				if ($user->recoveryExpiration > new DateTime()) {
					return $user;
				} else {
					$user->removeRecovery();
					$this->userRepo->save($user);
				}
			}
		}

		return NULL;
	}

	public function findByAccessToken($token)
	{
		if (!empty($token)) {
			$user = $this->userRepo->findOneBy([
				'accessToken' => $token
			]);

			if ($user) {
				if ($user->accessExpiration > new DateTime()) {
					return $user;
				} else {
					$user->removeRecovery();
					$this->userRepo->save($user);
				}
			}
		}

		return NULL;
	}

	public function findUnregisteredOrCreate($mail = NULL, $verificated = FALSE)
	{
		$user = NULL;
		if ($mail) {
			$user = $this->userRepo->findOneBy([
				'mail' => $mail,
				'verificated' => FALSE,
				'createdByAdmin' => TRUE,
			]);
		}
		if (!$user) {
			$user = new User($mail, $verificated);
		}
		return $user;
	}

}
