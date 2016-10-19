<?php

namespace App\Model\Entity\Traits;

use Doctrine\Common\Collections\Collection;

/**
 * @property Collection $jobs
 */
trait AdminJobs
{

	/** @ORM\OneToMany(targetEntity="Job", mappedBy="user") */
	protected $jobs;
}