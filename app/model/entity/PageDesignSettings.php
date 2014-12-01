<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * Page design settings (for Metronic)
 * @ORM\Entity
 * 
 * @property-read string $color
 * @property-read boolean $pageHeaderFixed
 * @property-read boolean $pageSidebarClosed
 * @property-read boolean $pageSidebarFixed
 * @property-read boolean $pageFooterFixed
 * @property-read boolean $pageSidebarReversed
 * @property-read boolean $pageFullWidth
 * @property-read boolean $pageContainerBgSolid
 * @property-read array $notNullValuesArray
 */
class PageDesignSettings extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\OneToOne(targetEntity="User", inversedBy="settings", fetch="LAZY")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	protected $user;

	/** @ORM\Column(type="string", length=50, nullable=true) */
	protected $color;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $pageHeaderFixed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $pageSidebarClosed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $pageSidebarFixed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $pageFooterFixed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $pageSidebarReversed;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $pageFullWidth;

	/** @ORM\Column(type="boolean", nullable=true) */
	protected $pageContainerBgSolid;

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
		if ($rewriteExisting || !$this->pageHeaderFixed) {
			$this->pageHeaderFixed = $entity->pageHeaderFixed;
		}
		if ($rewriteExisting || !$this->pageSidebarClosed) {
			$this->pageSidebarClosed = $entity->pageSidebarClosed;
		}
		if ($rewriteExisting || !$this->pageSidebarFixed) {
			$this->pageSidebarFixed = $entity->pageSidebarFixed;
		}
		if ($rewriteExisting || !$this->pageFooterFixed) {
			$this->pageFooterFixed = $entity->pageFooterFixed;
		}
		if ($rewriteExisting || !$this->pageSidebarReversed) {
			$this->pageSidebarReversed = $entity->pageSidebarReversed;
		}
		if ($rewriteExisting || !$this->pageFullWidth) {
			$this->pageFullWidth = $entity->pageFullWidth;
		}
		if ($rewriteExisting || !$this->pageContainerBgSolid) {
			$this->pageContainerBgSolid = $entity->pageContainerBgSolid;
		}
		return $this;
	}

}
