<?php

namespace App\Model\Facade;

use Kdyby\Doctrine\EntityDao,
	Kdyby\Doctrine\EntityManager,
	Kdyby\Doctrine\Entities\BaseEntity;

abstract class BaseFacade extends \Nette\Object
{
	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	/** @var EntityDao */
	protected $dao;

	/** @var EntityManager */
	protected $em;

	// </editor-fold>

	public function __construct(EntityDao $dao, EntityManager $em)
	{
		$this->dao = $dao;
		$this->em = $em;

		$this->init();
	}

	/**
	 * Method for initialization of other DAOs in extended facades.
	 */
	protected function init()
	{
		
	}

	// <editor-fold defaultstate="collapsed" desc="save">

	/**
	 * @param BaseEntity $entity
	 * @return BaseEntity
	 */
	public function save(BaseEntity $entity)
	{
		return $this->dao->save($entity);
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="finders">

	/**
	 * @param int $id
	 * @return BaseEntity
	 */
	public function find($id)
	{
		return $this->dao->findOneBy(array('id' => $id));
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

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="delete">

	/**
	 * @param BaseEntity $entity
	 * @return void
	 */
	public function delete(BaseEntity $entity)
	{
		$this->dao->delete($entity);
	}

	// </editor-fold>
}
