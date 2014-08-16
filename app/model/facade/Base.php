<?php

namespace App\Model\Facade;

abstract class Base extends \Nette\Object
{

    /** @var \Kdyby\Doctrine\EntityDao @deprecated */
    protected $dao;
	
	/** @var \Kdyby\Doctrine\EntityManager */
	protected $em;
	
    public function __construct(\Kdyby\Doctrine\EntityDao $dao, \Kdyby\Doctrine\EntityManager $em)
    {
        $this->dao = $dao;
		$this->em = $em;
		
		$this->init();
    }

	/** @deprecated */
    public function find($id)
    {
        return $this->dao->findOneBy(array('id' => $id));
    }

	/** @deprecated */
    public function save( $entity)
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

	/** @deprecated */
    public function delete(\Kdyby\Doctrine\Entities\IdentifiedEntity $entity)
    {
        return $this->dao->delete($entity);
    }

	protected function init()
	{
		
	}
}
