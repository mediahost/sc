<?php

namespace App\Model\Entity;

use App\Helpers;
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
 * @property User $user
 * @property string $firstname
 * @property string $middlename
 * @property string $surname
 * @property string $fullName
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
 * @property string $facebookLink
 * @property string $twitterLink
 * @property string $googleLink
 * @property string $linkedinLink
 * @property string $pinterestLink
 */
class Person extends BaseEntity
{

	use Identifier;

	/** @ORM\OneToOne(targetEntity="User", mappedBy="person", fetch="LAZY") */
	protected $user;

	/** @ORM\OneToOne(targetEntity="Candidate", inversedBy="person", fetch="LAZY", cascade={"persist", "remove"}, orphanRemoval=true) */
	protected $candidate;

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

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $facebookLink;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $twitterLink;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $googleLink;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $linkedinLink;

	/** @ORM\Column(type="string", length=256, nullable=true) */
	protected $pinterestLink;

	public function __toString()
	{
		return (string)$this->getFullName();
	}

	public function getCandidate()
	{
		if (!$this->candidate) {
			$this->candidate = new Candidate();
			$this->candidate->person = $this;
		}
		return $this->candidate;
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
		return $this->degreeBefore . ' ' . $this->getFullName() . ' ' . $this->degreeAfter;
	}

	public function isFilled()
	{
		return TRUE;
	}

	public function isRequiredOtherFilled()
	{
		return count($this->workLocations) && count($this->jobCategories);
	}

	public function setPhoto($file)
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

	public function getPhoto()
	{
		return $this->photo;
	}

	public function setFullName($value)
	{
		$splitted = preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY);
		if (count($splitted) >= 3) {
			$this->firstname = array_shift($splitted);
			$this->middlename = array_shift($splitted);
			$this->surname = implode(' ', $splitted);
		} else if (count($splitted) == 2) {
			$this->firstname = array_shift($splitted);
			$this->surname = implode(' ', $splitted);
		} else {
			$this->firstname = implode(' ', $splitted);
		}
		return $this;
	}

	public function getFullName()
	{
		return Helpers::concatStrings(' ', $this->firstname, $this->middlename, $this->surname);
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
