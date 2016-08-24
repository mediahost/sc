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
 * @property bool $freelancer
 * @property Address $address
 * @property Image $photo
 * @property Cv[] $cvs
 * @property Document[] $documents
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

	/** @ORM\Column(type="string", length=3, nullable=true) */
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

	/** @ORM\Column(type="boolean") */
	protected $freelancer = FALSE;

	/** @ORM\Column(type="array") */
	protected $workLocations;
    
    /** @ORM\Column(type="array") */
	protected $jobCategories;
    
    /** @ORM\Column(type="string", length=64, nullable=true) */
	protected $tags;

	/** @ORM\OneToMany(targetEntity="Cv", mappedBy="candidate", fetch="EAGER", cascade={"persist", "remove"}) */
	protected $cvs;

	/** @ORM\OneToMany(targetEntity="Document", mappedBy="candidate", fetch="EAGER", cascade={"persist", "remove"}) */
	protected $documents;

	/** @ORM\OneToOne(targetEntity="User", mappedBy="candidate", fetch="LAZY") */
	protected $user;

	public function __construct($name = NULL)
	{
		if ($name) {
			$this->name = $name;
		}
		$this->workLocations = [];
        $this->jobCategories = [];
		$this->cvs = new ArrayCollection();
		$this->documents = new ArrayCollection();
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
		return array_key_exists($this->title, $titles) ? $titles[$this->title] : NULL;
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

	public function isRequiredPersonalFilled()
	{
		return $this->photo && $this->address && $this->firstname && $this->surname && $this->phone;
	}

	public function isRequiredOtherFilled()
	{
		return  count($this->workLocations)  && count($this->jobCategories);
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

	public function addDocument($document) {
		$this->documents[] = $document;
	}

	public function removeDocument($documentId) {
		foreach ($this->documents as $document) {
			if($document->id == $documentId) {
				$this->documents->removeElement($document);
			}
		}
		return $this;
	}

	public function getDocuments() {
		return $this->documents;
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
		return [
			'mr' => 'Mr.',
			'mrs' => 'Mrs.',
			'ms' => 'Ms.',
		];
	}

	public static function getNationalityList()
	{
		return Address::getCountriesList();
	}

	public static function getLocalities($flat = FALSE)
	{
		$countries = [
			'European Union' => [
				2 => 'Austria',
				3 => 'Belgium',
				4 => 'Bulgaria',
				5 => 'Croatia',
				6 => 'Cyprus',
				7 => 'Czech Republic',
				8 => 'Denmark',
				9 => 'Estonia',
				10 => 'Finland',
				11 => 'France',
				12 => 'Germany',
				13 => 'Greece',
				14 => 'Hungary',
				15 => 'Ireland',
				16 => 'Italy',
				17 => 'Latvia',
				18 => 'Lithuania',
				19 => 'Luxembourg',
				20 => 'Malta',
				21 => 'Netherlands',
				22 => 'Poland',
				23 => 'Portugal',
				24 => 'Romania',
				25 => 'Slovakia',
				26 => 'Slovenia',
				27 => 'Spain',
				28 => 'Sweden',
				29 => 'United Kingdom',
			],
			'Rest of Europe' => [
				30 => 'Albania',
				31 => 'Armenia',
				32 => 'Azerbaijan',
				33 => 'Belarus',
				34 => 'Bosnia & Herzegovina',
				35 => 'Georgia',
				36 => 'Iceland',
				37 => 'Kazakhstan',
				38 => 'Macedonia',
				39 => 'Moldova',
				40 => 'Montenegro',
				41 => 'Russia',
				42 => 'Serbia',
				43 => 'Switzerland',
				44 => 'Turkey',
				45 => 'Ukraine',
			],
			'Middle East' => [
				46 => 'Israel',
				47 => 'United Arab Emirate',
				48 => 'Saudi Arabia',
				49 => 'Qatar',
			],
			'North America' => [
				50 => 'USA',
				51 => 'Canada',
			],
		];
		if ($flat) {
			$flatArray = [];
			foreach ($countries as $countryId => $country) {
				if (is_array($country)) {
					foreach ($country as $countryItemId => $countryItem) {
						$flatArray[$countryItemId] = $countryItem;
					}
				} else {
					$flatArray[$countryId] = $country;
				}
			}
			return $flatArray;
		} else {
			return $countries;
		}
	}

}
