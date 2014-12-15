<?php

namespace App\Components;

use Nette\InvalidArgumentException;
use Nette\Utils\ArrayHash;

/**
 * Entity Control
 * 
 * @author Petr Poupě
 */
abstract class EntityControl extends BaseControl
{

	protected $entity;

	/**
	 * @param $entity
	 * @throws InvalidArgumentException
	 * @return self
	 */
	public function setEntity($entity)
	{
		if (!$this->checkEntityType($entity)) {
			throw new InvalidArgumentException('Check is setted entity has right class. ' . get_class($entity) . ' given');
		}
		$this->entity = $entity;
		return $this;
	}

	/** Get entity */
	public function getEntity()
	{
		if ($this->entity) {
			return $this->entity;
		} else {
			return $this->getNewEntity();
		}
	}

	/** @return bool */
	public function isEntityExists()
	{
		return $this->getEntity()->id !== NULL;
	}
	
	/** 
	 * Get Entity for Form
	 * @return array 
	 */
	protected function getDefaults()
	{
		return [];
	}
	
	/**
	 * Load Entity from Form
	 * @param ArrayHash $values
	 */
	protected function load(ArrayHash $values)
	{
		$entity = $this->getEntity();
		return $entity;
	}

	/**
	 * Check if entity is instance of right entity
	 * @return bool
	 */
	abstract protected function checkEntityType($entity);

	/**
	 * Create new entity
	 */
	abstract protected function getNewEntity();
}
