<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * Page config settings
 * @ORM\Entity
 * 
 * @property User $user
 * @property-read string $language
 * @property-read array $notNullValuesArray return toArray only for setted values
 */
class PageConfigSettings extends BaseEntity
{

	use \Kdyby\Doctrine\Entities\Attributes\Identifier;

	/**
	 * @ORM\OneToOne(targetEntity="User", inversedBy="pageConfigSettings", fetch="LAZY")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	protected $user;

	/** @ORM\Column(type="string", length=8, nullable=true) */
	protected $language;

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
	 * @param \App\Model\Entity\PageConfigSettings $entity
	 * @param type $rewriteExisting
	 */
	public function append(PageConfigSettings $entity, $rewriteExisting = FALSE)
	{
		if ($rewriteExisting || $this->language === NULL) {
			$this->language = $entity->language;
		}
		return $this;
	}

}
