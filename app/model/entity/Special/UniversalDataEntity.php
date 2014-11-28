<?php

namespace App\Model\Entity\Special;

/**
 * UniversalDataEntity
 * Return setted data from array
 *
 * @author Petr Poupě
 */
class UniversalDataEntity
{

	/** @var array */
	private $data = [];

	public function __construct(array $data = [])
	{
		$this->setData($data);
	}

	public function __get($name)
	{
		if (array_key_exists($name, $this->data)) {
			return $this->data[$name];
		}
		return NULL;
	}
	
	public function setData(array $data)
	{
		$this->data = $data;
	}

}
