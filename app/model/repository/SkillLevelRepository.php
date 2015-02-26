<?php

namespace App\Model\Repository;

use Kdyby\Doctrine\EntityRepository;

class SkillLevelRepository extends EntityRepository
{

	public function findPairsName($priorityAsc = TRUE)
	{
		return $this->findPairs('name', ['priority' => $priorityAsc ? 'ASC' : 'DESC']);
	}

}
