<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property Company $company
 * @property ArrayCollection $roles
 */
class CompanyPriviledge extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;
	
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="allowedCompanies")
     **/
	protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Company", inversedBy="acceses")
     **/
	protected $company;

	/**
	 * @ORM\ManyToMany(targetEntity="CompanyRole")
	 */
	protected $roles;

	public function __construct()
	{
		parent::__construct();
		$this->roles = new ArrayCollection;
	}

}
