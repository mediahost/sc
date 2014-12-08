<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * Page design settings (for Metronic)
 * @ORM\Entity
 * 
 * @property-read string $color
 * @property-read boolean $layoutBoxed
 * @property-read boolean $containerBgSolid
 * @property-read boolean $headerFixed
 * @property-read boolean $footerFixed
 * @property-read boolean $sidebarClosed
 * @property-read boolean $sidebarFixed
 * @property-read boolean $sidebarReversed
 * @property-read boolean $sidebarMenuHover
 * @property-read boolean $sidebarMenuLight
 * @property-read array $notNullValuesArray
 */
class PageDesignSettings extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\OneToOne(targetEntity="User", inversedBy="pageDesignSettings", fetch="LAZY")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	protected $user;

	/** @ORM\Column(type="string", length=50, nullable=true) */
	protected $color;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $layoutBoxed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $containerBgSolid;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $headerFixed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $footerFixed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $sidebarClosed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $sidebarFixed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $sidebarReversed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $sidebarMenuHover;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $sidebarMenuLight;

	/**
	 * Set default value for entity
	 * @param array $values
	 * @return self
	 */
	public function setValues(array $values)
	{
		foreach ($values as $property => $value) {
			if ($this->getReflection()->hasProperty($property)) {
				$this->$property = $value;
			}
		}
		return $this;
	}

	public function getNotNullValuesArray()
	{
		return $this->toArray(TRUE);
	}

	public function toArray($onlyNotNull = FALSE)
	{
		$array = [];
		foreach ($this->getReflection()->getProperties() as $property) {
			if (!$onlyNotNull || ($onlyNotNull && $this->{$property->name} !== NULL)) {
				$array[$property->name] = $this->{$property->name};
			}
		}
		return $array;
	}
	
	/**
	 * Append entity data
	 * @param PageDesignSettings $entity
	 * @param type $rewriteExisting
	 */
	public function append(PageDesignSettings $entity, $rewriteExisting = FALSE)
	{
		if ($rewriteExisting || !$this->color) {
			$this->color = $entity->color;
		}
		if ($rewriteExisting || !$this->containerBgSolid) {
			$this->containerBgSolid = $entity->containerBgSolid;
		}
		if ($rewriteExisting || !$this->headerFixed) {
			$this->headerFixed = $entity->headerFixed;
		}
		if ($rewriteExisting || !$this->footerFixed) {
			$this->footerFixed = $entity->footerFixed;
		}
		if ($rewriteExisting || !$this->sidebarClosed) {
			$this->sidebarClosed = $entity->sidebarClosed;
		}
		if ($rewriteExisting || !$this->sidebarFixed) {
			$this->sidebarFixed = $entity->sidebarFixed;
		}
		if ($rewriteExisting || !$this->sidebarReversed) {
			$this->sidebarReversed = $entity->sidebarReversed;
		}
		if ($rewriteExisting || !$this->sidebarMenuHover) {
			$this->sidebarMenuHover = $entity->sidebarMenuHover;
		}
		if ($rewriteExisting || !$this->sidebarMenuLight) {
			$this->sidebarMenuLight = $entity->sidebarMenuLight;
		}
		return $this;
	}

}
