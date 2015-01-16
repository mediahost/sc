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

	const GUEST = 'guest';
	const SIGNED = 'signed';
	const CANDIDATE = 'candidate';
	const COMPANY = 'company';
	const ADMIN = 'admin';
	const SUPERADMIN = 'superadmin';

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
		return (string) $this->name;
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
			self::GUEST,
			self::SIGNED,
			self::CANDIDATE,
			self::COMPANY,
			self::ADMIN,
			self::SUPERADMIN,
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
