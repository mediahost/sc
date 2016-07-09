<?php

namespace App\Model\Entity;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * 
 * @property Job $job
 * @property Cv $cv
 * @property int $state
 */
class JobCv extends \Kdyby\Doctrine\Entities\BaseEntity {
    
    use Identifier;
    
    const CV_STATE_INVITED = 1;
    const CV_STATE_APLLIED = 2;
    const CV_STATE_MATCHED = 3;
    const CV_STATE_SHORTLISTED = 4;
    const CV_STATE_REJECTED = 5;
    const CV_STATE_INVITED_COMPANY = 6;
    const CV_STATE_OFFER_MADE = 7;
    const CV_STATE_HIRED = 8;
    
    /** 
	 * @ORM\ManyToOne(targetEntity="Job", inversedBy="cvs")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $job;
        
    /** 
	 * @ORM\ManyToOne(targetEntity="Cv", inversedBy="jobs")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $cv;
}
