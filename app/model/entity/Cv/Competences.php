<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\Json;

/**
 * @ORM\Entity
 *
 * @property string $social
 * @property string $organisation
 * @property string $technical
 * @property string $artictic
 * @property string $other
 * @property array $drivingLicenses
 * @property-read array $drivingLicensesNames
 */
class Competences extends BaseEntity
{

	use Identifier;

	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $social;

	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $organisation;

	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $technical;

	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $artictic;

	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $other;

	/** @ORM\Column(type="string", length=512, nullable=true) */
	protected $drivingLicenses;

	public function setDrivingLicenses(array $licenses)
	{
		$this->drivingLicenses = Json::encode($licenses);
		return $this;
	}

	public function getDrivingLicenses()
	{
		return Json::decode($this->drivingLicenses);
	}

	public function getDrivingLicensesNames()
	{
		$drivingLicenses = [];
		$licensesKeys = $this->getDrivingLicenses();
		$allLicenses = self::getDrivingLicensesList();
		if (is_array($licensesKeys)) {
			foreach ($licensesKeys as $licenseKey) {
				if (isset($allLicenses[$licenseKey])) {
					$drivingLicenses[$licenseKey] = $allLicenses[$licenseKey];
				}
			}
		}
		return $drivingLicenses;
	}

	public static function getDrivingLicensesList()
	{
		return [
			'a' => 'A',
			'a1' => 'A1',
			'b' => 'B',
			'b1' => 'B1',
			'be' => 'BE',
			'c' => 'C',
			'c1' => 'C1',
			'ce' => 'CE',
			'c1e' => 'C1E',
			'd' => 'D',
			'd1' => 'D1',
			'de' => 'DE',
			'd1e' => 'D1E',
		];
	}

}
