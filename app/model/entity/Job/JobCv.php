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
    
    const CV_STATE_FREE = 1;
    const CV_STATE_INVITED = 2;
    const CV_STATE_APLLIED = 3;
    const CV_STATE_MATCHED = 4;
    const CV_STATE_SHORTLISTED = 5;
    const CV_STATE_REJECTED = 6;
    const CV_STATE_INVITED_COMPANY = 7;
    const CV_STATE_OFFER_MADE = 8;
    const CV_STATE_HIRED = 9;
    
    /** 
	 * @ORM\ManyToOne(targetEntity="Job", inversedBy="cvs")
	 * @ORM\JoinColumn(onDelete="CASCADE", referencedColumnName="id")
	 */
	protected $job;
        
    /** 
	 * @ORM\ManyToOne(targetEntity="Cv", inversedBy="jobs")
	 * @ORM\JoinColumn(onDelete="CASCADE", referencedColumnName="id")
	 */
	protected $cv;
    
    /** @ORM\Column(type="smallint", nullable=false, options={"default" = 1})) */
	protected $state;
    
    public static function getStates() {
        return [
            self::CV_STATE_FREE => 'Free',
            self::CV_STATE_INVITED => 'Invited',
            self::CV_STATE_APLLIED => 'Apllied',
            self::CV_STATE_MATCHED => 'Matched',
            self::CV_STATE_SHORTLISTED => 'Shortlisted',
            self::CV_STATE_REJECTED => 'Rejected',
            self::CV_STATE_INVITED_COMPANY => 'Intervirew',
            self::CV_STATE_OFFER_MADE => 'Offer',
            self::CV_STATE_HIRED => 'Hired'
        ];
    }
}
