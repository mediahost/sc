<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityRepository;

abstract class BaseRepository extends EntityRepository implements IRepository
{

	public function save($entity)
	{
		$this->_em->persist($entity);
		$this->_em->flush();
		return $entity;
	}

}

interface IRepository
{

	public function save($entity);
}
