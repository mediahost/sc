<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property string $name
 * @property string $companyId
 * @property string $address
 * @property ArrayCollection $accesses
 * @property-read ArrayCollection $adminAccesses
 * @property-read ArrayCollection $managerAccesses
 * @property-read ArrayCollection $editorAccesses
 */
class Company extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\Column(type="string", length=512, nullable=false)
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string", length=100, nullable=true)
	 */
	protected $companyId;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $address;
	
	/** 
	 * @ORM\OneToMany(targetEntity="CompanyPermission", mappedBy="company", fetch="LAZY", cascade={"persist"}) 
	 */
	protected $accesses;

	/**
	 * @ORM\OneToMany(targetEntity="Job", mappedBy="company")
	 */
	protected $jobs;

	public function __construct($name = NULL)
	{
		parent::__construct();
		$this->accesses = new ArrayCollection;
		if ($name) {
			$this->name = $name;
		}
	}

	/** @return string */
	public function __toString()
	{
		return (string) $this->name;
	}
	
	public function addAccess(CompanyPermission $permission)
	{
		return $this->accesses->add($permission);
	}
	
	public function clearAccesses()
	{
		return $this->accesses->clear();
	}
	
	/** @return ArrayCollection */
	public function getAdminAccesses()
	{
		return $this->getAccessesFilter(CompanyRole::ADMIN);
	}
	
	/** @return ArrayCollection */
	public function getManagerAccesses()
	{
		return $this->getAccessesFilter(CompanyRole::MANAGER);
	}
	
	/** @return ArrayCollection */
	public function getEditorAccesses()
	{
		return $this->getAccessesFilter(CompanyRole::EDITOR);
	}
	
	/** @return ArrayCollection */
	private function getAccessesFilter($roleName)
	{
		return $this->accesses->filter(function ($permission) use ($roleName) {
			return $permission->containRoleName($roleName);
		});
	}

}
