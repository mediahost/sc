<?php

namespace App\Model\Entity\Traits;

use App\Model\Entity\Candidate;
use App\Model\Entity\Company;
use App\Model\Entity\CompanyPermission;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\Person;
use App\Model\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @property-read ArrayCollection $roles
 * @property-read array $rolesKeys
 * @property Role $maxRole
 * @property Person $person
 */
trait UserRoles
{

	/** @ORM\OneToOne(targetEntity="Person", inversedBy="user", fetch="LAZY", cascade={"persist", "remove"}, orphanRemoval=true) */
	protected $person;

	/** @ORM\ManyToMany(targetEntity="Role", fetch="EAGER", cascade={"persist"}) */
	private $roles;

	/** @ORM\OneToMany(targetEntity="CompanyPermission", mappedBy="user", fetch="LAZY", cascade={"persist"}) */
	protected $allowedCompanies;

	public function addRole(Role $role)
	{
		if (!$this->roles->contains($role)) {
			$this->roles->add($role);
			if ($role->name === Role::CANDIDATE) {
				$this->getCandidate();
			}
		}
		return $this;
	}

	public function addRoles(array $roles)
	{
		foreach ($roles as $role) {
			$this->addRole($role);
		}
		return $this;
	}

	public function clearRoles()
	{
		$this->roles->clear();
		return $this;
	}

	public function removeRole(Role $role)
	{
		$this->roles->removeElement($role);
		return $this;
	}

	public function isAdmin()
	{
		return $this->isInRole(Role::ADMIN);
	}

	public function isCompany()
	{
		return !$this->isInRole(Role::CANDIDATE) && $this->isInRole(Role::COMPANY);
	}

	public function isCandidate()
	{
		return $this->isInRole(Role::CANDIDATE);
	}

	public function isInRole($role)
	{
		return in_array($role, $this->getRoles(), TRUE);
	}

	public function getPerson()
	{
		if (!$this->person) {
			$this->person = new Person();
			$this->person->user = $this;
		}
		return $this->person;
	}

	public function getCandidate()
	{
		return $this->getPerson()->getCandidate();
	}

	public function getRolesKeys()
	{
		$array = [];
		foreach ($this->roles as $role) {
			$array[] = $role->id;
		}
		return $array;
	}

	/** @return array with roleID => roleName */
	public function getRoles()
	{
		$array = [];
		foreach ($this->roles as $role) {
			if ($role->id) {
				$array[$role->id] = $role->name;
			} else {
				$array[] = $role->name;
			}
		}
		return $array;
	}

	public function getMaxRole()
	{
		return Role::getMaxRole($this->roles->toArray());
	}

	/** @return Company[] */
	public function getCompanies(CompanyRole $role = NULL)
	{
		$companies = [];
		/** @var CompanyPermission $companyPermission */
		foreach ($this->allowedCompanies as $companyPermission) {
			if ($role && $companyPermission->isInRole($role)) {
				$companies[$companyPermission->company->id] = $companyPermission->company;
			} else {
				$companies[$companyPermission->company->id] = $companyPermission->company;
			}
		}
		return $companies;
	}

}