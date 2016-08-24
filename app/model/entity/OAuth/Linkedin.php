<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @property string $accessToken
 * @property string $mail
 * @property string $name
 * @property string $birthday
 * @property string $gender
 * @property string $hometown
 * @property string $link
 * @property string $location
 * @property string $locale
 * @property string $username
 */
class Linkedin extends OAuth
{

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $mail;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $firstname;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $surname;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $name;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $headline;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $industry;

	/** @ORM\Column(type="text", nullable=true) */
	protected $summary;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $pictureUrl;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $publicProfileUrl;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $siteStandardProfileRequest;

	/** @ORM\Column(type="string", length=5, nullable=true) */
	protected $locationCode;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $locationName;

	public function __construct($id = NULL)
	{
		if ($id) {
			$this->id = $id;
		}
		parent::__construct();
	}

	public function __toString()
	{
		return (string) $this->name;
	}

}
