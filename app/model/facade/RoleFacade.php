<?php

namespace App\Model\Facade;

use App\Extensions\Settings\Model\Service\ModuleService;
use App\Extensions\Settings\Model\Storage\DefaultSettingsStorage;
use App\Model\Entity\Role;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;
use Nette\Utils\ArrayHash;

/**
 * RoleFacade
 */
class RoleFacade extends Object
{

	/** @var EntityManager @inject */
	public $em;

	/** @var ModuleService @inject */
	public $moduleService;

	/** @var EntityDao */
	private $roleDao;
	
	public function __construct(EntityManager $em, ModuleService $modules)
	{
		$this->em = $em;
		$this->moduleService = $modules;
		$this->roleDao = $this->em->getDao(Role::getClassName());
	}

	// <editor-fold defaultstate="expanded" desc="create">

	/**
	 * Create role if is not exists.
	 * @param type $name
	 * @return Role|null
	 */
	public function create($name)
	{
		if ($this->isUnique($name)) {
			$entity = new Role($name);
			return $this->roleDao->save($entity);
		}
		return NULL;
	}

	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="getters">

	/**
	 * Get all roles
	 * @return array
	 */
	public function getRoles()
	{
		return $this->roleDao->findPairs([], 'name', [], 'id');
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="finders">

	/**
	 * Find role by name.
	 * @param type $name
	 * @return Role
	 */
	public function findByName($name)
	{
		return $this->roleDao->findOneBy(['name' => $name]);
	}

	/**
	 * Find all lower roles
	 * @param array $roles
	 * @return array
	 */
	public function findLowerRoles(array $roles, $includeMax = FALSE)
	{
		$allRoles = $this->roleDao->findPairs('name', 'id'); // expect roles by priority (first is the lowest)
		$lowerRoles = [];
		$maxRole = Role::getMaxRole($roles);
		if (in_array($maxRole->name, $allRoles)) {
			foreach ($allRoles as $id => $dbRole) {
				if ($maxRole->name === $dbRole) {
					if ($includeMax) {
						$lowerRoles[$id] = $dbRole;
					}
					break;
				}
				$lowerRoles[$id] = $dbRole;
			}
		}
		return $lowerRoles;
	}

	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="checkers">

	/**
	 * Check if name is unique.
	 * @param type $name
	 * @return bool
	 */
	public function isUnique($name)
	{
		return $this->findByName($name) === NULL;
	}

	/**
	 * Check if role is allowed to register.
	 * @param string $roleName
	 * @return Role|FALSE
	 */
	public function isRegistrable($roleName)
	{
		if ($this->moduleService->isAllowedModule('registrableRole')) {
			$role = $this->findByName($roleName);

			$registrable = $this->moduleService->getModuleSettings('registrableRole')->roles;
			if ($registrable instanceof ArrayHash) {
				$registrable = (array) $registrable;
			}
			if ($role !== NULL && (is_array($registrable) && in_array($role->name, $registrable))) {
				return $role;
			}
		}
		return FALSE;
	}

	// </editor-fold>
}
