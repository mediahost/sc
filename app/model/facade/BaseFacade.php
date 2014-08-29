<?php

namespace App\Model\Facade;

use Kdyby\Doctrine\EntityDao,
	Kdyby\Doctrine\EntityManager,
	Kdyby\Doctrine\Entities\BaseEntity;

abstract class BaseFacade extends \Nette\Object
{

	/** @var EntityDao */
	protected $dao;

	/** @var EntityManager */
	protected $em;

	public function __construct(EntityDao $dao, EntityManager $em)
	{
		$this->dao = $dao;
		$this->em = $em;

		$this->init();
	}

	/**
	 * @param int $id
	 * @return BaseEntity
	 */
	public function find($id)
	{
		return $this->dao->findOneBy(array('id' => $id));
	}

	/**
	 * @param BaseEntity $entity
	 * @return BaseEntity
	 */
	public function save(BaseEntity $entity)
	{
		return $this->dao->save($entity);
	}
	
	/**
	 * @param BaseEntity $entity
	 * @return void
	 */
	public function delete(BaseEntity $entity)
	{
		$this->dao->delete($entity);
	}

	/**
	 * @param int|string $value
	 * @param array $orderBy
	 * @param int|string $key
	 * @return BaseEntity
	 */
	public function findPairs($value, $orderBy = ["id" => "asc"], $key = "id")
	{
		return $this->dao->findPairs(array(), $value, $orderBy, $key);
	}

	/**
	 * @return array
	 */
	public function findAll()
	{
		return $this->dao->findAll();
	}

	/**
	 * Method for initialization of other DAOs in extended facades.
	 */
	protected function init()
	{}

}
