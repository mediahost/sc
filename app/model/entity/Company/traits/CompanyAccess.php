<?php

namespace App\Model\Entity\Traits;

use App\Model\Entity\CompanyPermission;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\User;
use Doctrine\Common\Collections\Collection;

/**
 * @property Collection $accesses
 * @property-read User $delegate
 * @property-read Collection $adminAccesses
 * @property-read Collection $managerAccesses
 * @property-read Collection $editorAccesses
 */
trait CompanyAccess
{

	/**  @ORM\OneToMany(targetEntity="CompanyPermission", mappedBy="company", fetch="LAZY", cascade={"persist"}) */
	private $accesses;

	/** @return User */
	public function getDelegate()
	{
		$adminPermissions = $this->getAdminAccesses();
		$adminPermission = $adminPermissions->first();
		return $adminPermission->user;
	}

	/** @return Collection */
	public function getAccesses()
	{
		return $this->accesses;
	}

	public function addAccess(CompanyPermission $permission)
	{
		return $this->accesses->add($permission);
	}

	public function clearAccesses()
	{
		return $this->accesses->clear();
	}

	public function getAdminAccesses()
	{
		return $this->getAccessesFilter(CompanyRole::ADMIN);
	}

	public function getManagerAccesses()
	{
		return $this->getAccessesFilter(CompanyRole::MANAGER);
	}

	public function getEditorAccesses()
	{
		return $this->getAccessesFilter(CompanyRole::EDITOR);
	}

	private function getAccessesFilter($roleName)
	{
		$containsRole = function ($permission) use ($roleName) {
			return $permission->containRoleName($roleName);
		};
		return $this->accesses->filter($containsRole);
	}

}