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
 * @property-read Collection $mesengerAccesses
 * @property-read Collection $jobberAccesses
 * @property-read Collection $editorAccesses
 */
trait CompanyAccess
{

	/**  @ORM\OneToMany(targetEntity="CompanyPermission", mappedBy="company", fetch="LAZY", cascade={"persist"}) */
	private $accesses;

	/** @return User */
	public function getDelegate()
	{
		$permissions = $this->getAdminAccesses();
		if ($permissions->count()) {
			$permission = $permissions->first();
		} else {
			$permissions = $this->getManagerAccesses();
			if ($permissions->count()) {
				$permission = $permissions->first();
			} else {
				$permissions = $this->getJobberAccesses();
				if ($permissions->count()) {
					$permission = $permissions->first();
				} else {
					$permissions = $this->getEditorAccesses();
					$permission = $permissions->first();
				}
			}
		}
		return $permission->user;
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

	public function getJobberAccesses()
	{
		return $this->getAccessesFilter(CompanyRole::JOBBER);
	}

	public function getMessengerAccesses()
	{
		return $this->getAccessesFilter(CompanyRole::MESSENGER);
	}

	private function getAccessesFilter($roleName)
	{
		$containsRole = function ($permission) use ($roleName) {
			return $permission->containRoleName($roleName);
		};
		return $this->accesses->filter($containsRole);
	}

}