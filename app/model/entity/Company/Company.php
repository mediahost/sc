<?php

namespace App\Model\Entity;

use App\Model\Entity\Traits\CompanyAccess;
use App\Model\Entity\Traits\CompanyJobs;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Http\FileUpload;
use Nette\Utils\Strings;

/**
 * @ORM\Entity
 *
 * @property string $name
 * @property string $companyId
 * @property string $address
 * @property Image $logo
 */
class Company extends BaseEntity
{

	use Identifier;
	use CompanyAccess;
	use CompanyJobs;

	/** @ORM\Column(type="string", length=512, nullable=false) */
	protected $name;

	/** @ORM\Column(type="string", length=100, nullable=true) */
	protected $companyId;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $mail;

	/** @ORM\Column(type="text", nullable=true) */
	protected $address;

	/** @ORM\OneToOne(targetEntity="Image", cascade="all") */
	protected $logo;

	/** @ORM\ManyToMany(targetEntity="Image") */
	protected $images;

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		$this->accesses = new ArrayCollection();
		$this->jobs = new ArrayCollection();
		$this->images = new ArrayCollection();
		parent::__construct();
	}

	public function __toString()
	{
		return (string)$this->name;
	}

	public function isNew()
	{
		return $this->id === NULL;
	}

	public function setLogo(FileUpload $file)
	{
		if (!$this->logo instanceof Image) {
			$this->logo = new Image($file);
		} else {
			$this->logo->setFile($file);
		}
		$this->logo->requestedFilename = 'company_logo_' . Strings::webalize(microtime());
		$this->logo->setFolder(Image::FOLDER_COMPANY_LOGO);
		return $this;
	}

}
