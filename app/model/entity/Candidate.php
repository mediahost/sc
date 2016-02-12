<?php

namespace App\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Http\FileUpload;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

/**
 * @ORM\Entity
 *
 * @property string $firstname
 * @property string $middlename
 * @property string $surname
 * @property DateTime $birthday
 * @property string $title
 * @property-read array $titles
 * @property string $gender
 * @property-read array $genders
 * @property string $degreeBefore
 * @property string $degreeAfter
 * @property-read string $degreeName
 * @property string $nationality
 * @property string $phone
 * @property Address $address
 * @property Image $photo
 * @property Cv[] $cvs
 * @property User $user
 */
class Candidate extends BaseEntity
{

	use Identifier;

	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $firstname;
		
	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $middlename;
	
	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $surname;

	/** @ORM\Column(type="date", nullable=true) */
	protected $title;
	
	/** @ORM\Column(type="date", nullable=true) */
	protected $birthday;

	/** @ORM\Column(type="string", length=1, nullable=true) */
	protected $gender = 'x';

	/** @ORM\Column(type="string", length=50, nullable=true) */
	protected $degreeBefore;

	/** @ORM\Column(type="string", length=50, nullable=true) */
	protected $degreeAfter;

	/** @ORM\Column(type="string", length=5, nullable=true) */
	protected $nationality;

	/** @ORM\Column(type="string", length=50, nullable=true) */
	protected $phone;

	/** @ORM\OneToOne(targetEntity="Image", cascade="all") */
	protected $photo;

	/** @ORM\OneToOne(targetEntity="Address", cascade="all", fetch="LAZY") */
	protected $address;

	/** @ORM\OneToMany(targetEntity="Cv", mappedBy="candidate", fetch="EAGER", cascade={"persist", "remove"}) */
	protected $cvs;

	/** @ORM\OneToOne(targetEntity="User", mappedBy="candidate", fetch="LAZY") */
	protected $user;

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		$this->cvs = new ArrayCollection;
		parent::__construct();
	}

	public function __toString()
	{
		return (string) $this->name;
	}

	public function hasDefaultCv()
	{
		$isDefault = function (Cv $cv) {
			return $cv->isDefault;
		};
		return $this->cvs->exists($isDefault);
	}

	public function getDefaultCv()
	{
		foreach ($this->cvs as $cv) {
			if ($cv->isDefault) {
				return $cv;
			}
		}
		throw new EntityException('Candidate has no default CV');
	}

	public function getGenderName()
	{
		$genders = self::getGenderList();
		return array_key_exists($this->gender, $genders) ? $genders[$this->gender] : NULL;
	}
	
	public function getTitleName()
	{
		$titles = self::getTitleList();
		return array_key_exists($this->title, $titles)  ?  $titles[$this->title] : NULL;
	}

	public function getNationalityName()
	{
		$nationalities = self::getNationalityList();
		return array_key_exists($this->nationality, $nationalities) ? $nationalities[$this->nationality] : NULL;
	}

	public function getDegreeName()
	{
		return $this->degreeBefore . ' ' . $this->name . ' ' . $this->degreeAfter;
	}

	public function setPhoto(FileUpload $file)
	{
		if (!$this->photo instanceof Image) {
			$this->photo = new Image($file);
		} else {
			$this->photo->setFile($file);
		}
		$this->photo->requestedFilename = 'candidate_photo_' . Strings::webalize(microtime());
		$this->photo->setFolder(Image::FOLDER_CANDIDATE_IMAGE);
		return $this;
	}
	
	public function getName()
	{
		return sprintf('%s %s %s', $this->firstname, $this->middlename, $this->surname);
	}

	public static function getGenderList()
	{
		return [
			'm' => 'Male',
			'f' => 'Female',
			'x' => 'Not disclosed',
		];
	}
	
	public static function getTitleList()
	{
		return ['Mr.', 'Mrs.', 'Ms.'];
	}

	public static function getNationalityList()
	{
		return Address::getCountriesList();
	}

}
