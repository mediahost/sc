<?php

namespace App\Model\Entity;

use App\Extensions\FotoHelpers;
use App\Helpers;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Http\FileUpload;
use Nette\Utils\DateTime;
use Nette\Utils\Image as ImageUtils;
use Nette\Utils\Random;

/**
 * @ORM\Entity
 * @ORM\EntityListeners({"App\Listeners\Model\Entity\ImageListener"})
 *
 * @property string|FileUpload $filename
 * @property FileUpload|ImageUtils $source
 * @property-read bool $changed
 */
class Image extends BaseEntity
{

	const FOLDER_DEFAULT = 'others';
	const FOLDER_COMPANY_LOGO = 'companies/logos';
	const FOLDER_CANDIDATE_IMAGE = 'candidates/images';
	const DEFAULT_IMAGE = 'default.png';
	const DEFAULT_IMAGE_URL = 'https://api.adorable.io/avatars';

	use Identifier;

	/** @ORM\Column(type="string", length=256, nullable=false) */
	protected $filename;

	/** @ORM\Column(type="datetime") */
	protected $lastChange;

	/** @var FileUpload */
	private $file = NULL;

	/** @var ImageUtils */
	private $image = NULL;

	/** @var string */
	protected $requestedFilename;

	/** @var string */
	private $folderToSave = self::FOLDER_DEFAULT;

	public function __construct($source)
	{
		$this->setSource($source);
		parent::__construct();
	}

	public function __toString()
	{
		return $this->filename ? (string)$this->filename : self::getDefaultImage();
	}

	public function setSource($source)
	{
		if ($source instanceof FileUpload) {
			$this->setFile($source);
		} else if ($source instanceof ImageUtils) {
			$this->setImage($source);
		} else if (is_string($source)) {
			$this->filename = $source;
		}
		return $this;
	}

	public function setFile(FileUpload $file, $requestedFilename = NULL)
	{
		$this->file = $file;
		$this->image = NULL;
		$this->requestedFilename = $requestedFilename;
		$this->actualizeLastChange();
		return $this;
	}

	public function setImage(ImageUtils $image, $requestedFilename = NULL)
	{
		$this->image = $image;
		$this->file = NULL;
		$this->requestedFilename = $requestedFilename;
		$this->actualizeLastChange();
		return $this;
	}

	public function getSource()
	{
		if ($this->file) {
			return $this->file;
		} else if ($this->image) {
			return $this->image;
		} else {
			return NULL;
		}
	}

	private function actualizeLastChange()
	{
		$this->lastChange = new DateTime();
		return $this;
	}

	public function setFolder($folder = self::FOLDER_DEFAULT)
	{
		switch ($folder) {
			case self::FOLDER_COMPANY_LOGO:
			case self::FOLDER_CANDIDATE_IMAGE:
			case self::FOLDER_DEFAULT:
				$this->folderToSave = $folder;
				break;
			default:
				$this->folderToSave = self::FOLDER_DEFAULT;
				break;
		}
		return $this;
	}

	public function getRequestedFilename()
	{
		if ($this->requestedFilename) {
			return $this->requestedFilename;
		} else if ($this->file instanceof FileUpload && $this->file->name) {
			return FotoHelpers::getFilenameWithoutExt($this->file->name);
		} else if ($this->file->name) {
			return Random::generate();
		}
	}

	public function getFolder()
	{
		return $this->folderToSave;
	}

	public function isChanged()
	{
		if ($this->file instanceof FileUpload) {
			return (bool)$this->file->isImage();
		} else if ($this->image instanceof ImageUtils) {
			return TRUE;
		}
		return FALSE;
	}

	public static function returnSizedFilename($image, $sizeX = NULL, $sizeY = NULL)
	{
		$size = NULL;
		if ($sizeX) {
			$sizeY = $sizeY ? $sizeY : '0';
			$size = $sizeX . FotoHelpers::getSizeSeparator() . $sizeY;
		}
		if ($image instanceof Image) {
			$filename = (string)$image;
		} else if (is_string($image) && !empty($image)) {
			$filename = $image;
		} else {
			$filename = self::getDefaultImage();
		}
		return Helpers::getPath($size, $filename);
	}

	public static function getDefaultImage($id = NULL, $size = NULL, $gender = NULL)
	{
		if ($gender === 'f') {
			$gender = 'female';
			$id %= 2;
		} else {
			$gender = 'male';
			$id %= 4;
		}
		return Helpers::concatStrings('/', 'avatars', $gender, 'user' . $id . '.png');
	}

}
