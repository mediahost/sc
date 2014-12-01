<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property string $name
 */
class Role extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	const ROLE_GUEST = 'guest';
	const ROLE_SIGNED = 'signed';
	const ROLE_CANDIDATE = 'candidate';
	const ROLE_COMPANY = 'company';
	const ROLE_ADMIN = 'admin';
	const ROLE_SUPERADMIN = 'superadmin';

	/** @ORM\Column(type="string", length=128) */
	protected $name;

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		parent::__construct();
	}

	public function __toString()
	{
		return $this->name;
	}

	/**
	 * Get max role from inserted roles
	 * If collection, then gets only last item
	 * @param array $roles
	 * @return Role
	 */
	public static function getMaxRole($roles)
	{
		$maxRole = new Role;
		if ($roles instanceof Collection) {
			return $roles->last();
		} else if (is_array($roles)) {
			usort($roles, ['App\Model\Entity\Role', 'cmpRoles']);
			$max = end($roles);
			if ($max instanceof Role) {
				$maxRole = $max;
			} else {
				$maxRole->name = (string) end($roles);
			}
		}
		return $maxRole;
	}

	/**
	 * Compare roles
	 * @param Role $roleA
	 * @param Role $roleB
	 * @return int
	 */
	public static function cmpRoles($roleA, $roleB)
	{
		$roleOrder = [
			self::ROLE_GUEST,
			self::ROLE_SIGNED,
			self::ROLE_CANDIDATE,
			self::ROLE_COMPANY,
			self::ROLE_ADMIN,
			self::ROLE_SUPERADMIN,
		];
		$roleAName = $roleA instanceof Role ? $roleA->name : (string) $roleA;
		$roleBName = $roleB instanceof Role ? $roleB->name : (string) $roleB;

		$roleAPosition = array_search($roleAName, $roleOrder);
		$roleBPosition = array_search($roleBName, $roleOrder);

		if ($roleAPosition == $roleBPosition) {
			return 0;
		}
		return ($roleAPosition < $roleBPosition) ? -1 : 1;
	}

}
