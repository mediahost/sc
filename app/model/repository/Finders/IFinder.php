<?php

namespace App\Model\Repository\Finders;

interface IFinder
{

	public function getQuery();

	public function getResult();
}
