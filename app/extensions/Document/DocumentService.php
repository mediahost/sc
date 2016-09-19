<?php

namespace App\Extensions;

use App\Helpers;
use App\Model\Entity\Document;
use Nette\Http\FileUpload;
use Nette\Object;
use Nette\Utils\DateTime;
use Nette\Utils\FileSystem;

class DocumentService extends Object
{
	/** @var string */
	private $rootFolder;

	/** @var string */
	private $webPath;

	/**
	 * @param FileUpload $file
	 * @return string
	 */
	public function uploadFile(FileUpload $file) {
		$dateTime = new DateTime();
		$uploadName = $file->getSanitizedName();
		$fileName = sprintf('%s_%s', $dateTime->getTimestamp(), $uploadName);
		$path = Helpers::getPath($this->rootFolder, $fileName);
		$file->move($path);
		return $fileName;
	}

	/** @param string $fileName  */
	public function deleteFile($fileName) {
		$path = Helpers::getPath($this->rootFolder, $fileName);
		if(file_exists($path)) {
			unlink($path);
		}
	}

	/**
	 * @param Document $document
	 * @return string
	 */
	public function applyWebPath(Document $document) {
		$path = $this->webPath . '/' . $document->name;
		$document->setWebPath($path);
	}

	/**
	 * @param string $folder
	 * @return $this
	 */
	public function setFolders($folder)
	{
		$this->rootFolder = $folder;
		FileSystem::createDir($folder);
		return $this;
	}

	public function setWebPath($path) {
		$this->webPath = $path;
	}
}