<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 *
 * @property string $name
 * @property DateTime $birthday
 * @property Cv $defaultCv
 */
class Candidate extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $name;

	/** @ORM\Column(type="date", nullable=true) */
	protected $birthday;
	
	/** @ORM\OneToMany(targetEntity="Cv", mappedBy="candidate", fetch="EAGER", cascade={"persist", "remove"}) */
	protected $cvs;
	
	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		parent::__construct();
		$this->cvs = new ArrayCollection;
	}

	/** @return string */
	public function __toString()
	{
		return (string) $this->name;
	}
	
	/**
	 * Check if candidate has any default cv
	 * @return boolean
	 */
	public function hasDefaultCv()
	{
		foreach ($this->cvs as $cv) {
			if ($cv->isDefault) {
				return TRUE;
			}
		}
		return FALSE;
	}
	
	/**
	 * Return default CV or NULL
	 * @return Cv|NULL
	 */
	public function getDefaultCv()
	{
		foreach ($this->cvs as $cv) {
			if ($cv->isDefault) {
				return $cv;
			}
		}
		return NULL;
	}

}
