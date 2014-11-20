<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * 
 * @property string $accessToken
 */
class Facebook extends OAuth
{
	
	public function __construct($id = NULL)
	{
		if ($id) {
			$this->id = $id;
		}
		parent::__construct();
	}

	/**
	 * @ORM\OneToOne(targetEntity="User", inversedBy="facebook", fetch="LAZY")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	protected $user;
	
	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $accessToken;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $mail;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $name;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $birthday;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $gender;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $hometown;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $link;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $location;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $locale;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $username;
}
