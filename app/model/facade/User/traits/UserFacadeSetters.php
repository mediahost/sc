<?php

namespace App\Model\Facade\Traits;

use App\Model\Entity\Role;
use App\Model\Entity\User;
use InvalidArgumentException;
use Nette\Utils\DateTime;
use Nette\Utils\Image;

trait UserFacadeSetters
{

	/**
	 * Add role as Role entity, string or array of entites to user.
	 * @param User $user
	 * @param Role|string|array $role
	 * @return User
	 * @throws InvalidArgumentException
	 */
	public function addRole(User $user, $role)
	{
		if (is_string($role) || $role instanceof Role) {
			return $user->addRole($this->roleDao->findOneBy(['name' => (string)$role]));
		} elseif (is_array($role)) {
			return $user->addRoles($this->roleDao->findBy(['name' => $role]));
		} else {
			throw new InvalidArgumentException();
		}
	}

	public function importSocialData(User $user)
	{
		if ($user->person) {
			if (!$user->person->fullName && $user->getSocialName()) {
				$user->person->fullName = $user->getSocialName();
			}
			if (!$user->person->birthday && $user->getSocialBirthday()) {
				$user->person->birthday = new DateTime($user->getSocialBirthday());
			}
			if (!$user->person->getPhoto(TRUE) && $user->getSocialPhotoUrl()) {
				$source = file_get_contents($user->getSocialPhotoUrl());
				$image = Image::fromString($source);
				$user->person->photo = $image;
			}
		}

		if ($user->id) {
			$this->userRepo->save($user);
		}
		return $this;
	}

}
