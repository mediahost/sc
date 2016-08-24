<?php

namespace App\Model\Facade;

use App\Extensions\Settings\SettingsStorage;
use App\Model\Entity\Role;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class RoleFacade extends Object
{

	/** @var EntityManager @inject */
	public $em;

	/** @var SettingsStorage @inject */
	public $settings;

	/** @var EntityDao */
	private $roleDao;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->roleDao = $this->em->getDao(Role::getClassName());
	}

	/**
	 * Create role if is not exists.
	 * @param string $name
	 * @return Role|null
	 */
	public function create($name)
	{
		if ($this->isUnique($name)) {
			$entity = new Role($name);
			$this->em->persist($entity);
			$this->em->flush();
			return $entity;
		}
		return NULL;
	}

	public function findByName($name)
	{
		return $this->roleDao->findOneBy(['name' => $name]);
	}

	public function isUnique($name)
	{
		return $this->findByName($name) === NULL;
	}

	public function isRegistrable($roleName)
	{
		try {
			$role = $this->findByName($roleName);
			$registrableRoles = $this->settings->getModules()->registrableRoles;
			return $this->isInRegistrable($role, (array) $registrableRoles);
		} catch (\ErrorException $e) {
			return FALSE;
		}
	}

	private function isInRegistrable(Role $role, array $registrableRoles)
	{
		return in_array($role->name, $registrableRoles);
	}

}
