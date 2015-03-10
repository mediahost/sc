<?php

namespace App\Model\Entity\Traits;

use Doctrine\Common\Collections\Collection;

/**
 * @property Collection $jobs
 */
trait CompanyJobs
{

	/** @ORM\OneToMany(targetEntity="Job", mappedBy="company") */
	private $jobs;

	/** @return Collection */
	public function getJobs()
	{
		return $this->jobs;
	}

}