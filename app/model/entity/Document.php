<?php
namespace App\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\Attributes\Identifier;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Http\FileUpload;

/**
 * @ORM\Entity
 * @ORM\EntityListeners({"App\Listeners\Model\Entity\DocumentListener"})
 *
 * @property Candidate $candidate
 * @property string $name
 * @property string $path
 * @property boolean $public
 */
class Document extends BaseEntity
{
	use Identifier;

	/** @ORM\ManyToOne(targetEntity="Candidate", inversedBy="documents") */
	protected $candidate;

	/** @ORM\Column(type="string", length=64) */
	protected $name;

	/** @ORM\Column(type="boolean", options={"default" : true}) */
	protected $public;

	/** @var FileUpload */
	protected $file;

	/** @var string */
	protected $webPath;


	public function __construct()
	{
		parent::__construct();
	}

	public function __toString()
	{
		return $this->name;
	}

	public function setFile(FileUpload $file, $requestedFilename = NULL)
	{
		$this->file = $file;
		return $this;
	}

	public function setWebPath($path) {
		$this->webPath = $path;
		return $this;
	}

	public function getWebPath() {
		return $this->webPath;
	}

	public function getDisplayName() {
		$displayName = '';
		sscanf($this->name, "%d_%s", $timestamp, $displayName);
		return $displayName;
	}

	public function fileExtension()
	{
		$ext = pathinfo($this->name, PATHINFO_EXTENSION);
		switch (strtolower($ext)) {
			case 'jpg':
				return 'jpg';
			case 'png':
				return 'png';
			case 'pdf':
				return 'pdf';
			default:
				return 'default';
		}
	}
}