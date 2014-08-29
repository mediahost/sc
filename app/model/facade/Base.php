<?php

namespace App\Model\Facade;

abstract class Base extends \Nette\Object
{

	/** @var \Kdyby\Doctrine\EntityManager */
	protected $em;

	public function __construct(\Kdyby\Doctrine\EntityManager $em)
	{
		$this->em = $em;

		$this->init();
	}

	/** @deprecated */
	public function find($id)
	{
		return $this->dao->findOneBy(array('id' => $id));
	}

	/** @deprecated */
	public function save($entity)
	{
		return $this->dao->save($entity);
	}

	/** @deprecated */
	public function findPairs($value, $orderBy = ["id" => "asc"], $key = "id")
	{
		return $this->dao->findPairs(array(), $value, $orderBy, $key);
	}

	/** @deprecated */
	public function findAll()
	{
		return $this->dao->findAll();
	}

	protected function init()
	{
		
	}

}
