<?php

namespace App\Model\Repository;

class SkillLevelRepository extends BaseRepository
{

	public function findPairsName($priorityAsc = TRUE)
	{
		return $this->findPairs('name', ['priority' => $priorityAsc ? 'ASC' : 'DESC']);
	}

}
