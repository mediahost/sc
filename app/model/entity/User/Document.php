<?php
namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity
 *
 * @property Candidate $candidate
 * @property string $name
 */
class Document extends BaseEntity
{
    use Identifier;


    /** @ORM\ManyToOne(targetEntity="Candidate", inversedBy="documents") */
    protected $candidate;

    /** @ORM\Column(type="string", length=64, nullable=false) */
    protected $name;


    public function __construct($name)
    {
        parent::__construct();
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }
}