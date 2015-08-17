<?php

namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Http\FileUpload;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity
 * @ORM\EntityListeners({"App\Listeners\Model\Entity\ImageListener"})
 *
 * @property string $filename
 * @property FileUpload $filename
 * @property-read bool $changed
 */
class Image extends BaseEntity
{

	const FOLDER_DEFAULT = 'others';
	const FOLDER_COMPANY_LOGO = 'companies/logos';
	const FOLDER_CANDIDATE_IMAGE = 'candidates/images';

	use Identifier;

	/** @ORM\Column(type="string", length=256, nullable=false) */
	protected $filename;

	/** @ORM\Column(type="date") */
	protected $lastChange;

	/** FileUpload */
	public $file;

	/** string */
	public $requestedFilename;

	/** string */
	private $folderToSave = self::FOLDER_DEFAULT;

	public function __construct($file)
	{
		if ($file instanceof FileUpload) {
			$this->setFile($file);
		} else if (is_string($file)) {
			$this->filename = $file;
		}
		parent::__construct();
	}

	public function __toString()
	{
		return (string) $this->filename;
	}

	public function setFile(FileUpload $file, $requestedFilename = NULL)
	{
		$this->file = $file;
		$this->requestedFilename = $requestedFilename;
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
	
	public function getFolder()
	{
		return $this->folderToSave;
	}

	public function isChanged()
	{
		return (bool) $this->file->isImage();
	}

}
