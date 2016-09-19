<?php

namespace App\Model\Entity;

use App\Model\Entity\Traits\CompanyPermissionRoles;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity(repositoryClass="App\Model\Repository\CompanyPermissionRepository")
 *
 * @property User $user
 * @property Company $company
 */
class CompanyPermission extends BaseEntity
{

	use Identifier;
	use CompanyPermissionRoles;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="allowedCompanies")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $user;

	/**
	 * @ORM\ManyToOne(targetEntity="Company", inversedBy="acceses")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $company;

	public function __construct()
	{
		$this->roles = new ArrayCollection();
		parent::__construct();
	}

}
