<?php

namespace App\Extensions;

use App\Helpers;
use App\Model\Entity\Document;
use Latte\Object;
use Nette\Http\FileUpload;
use Nette\Utils\DateTime;
use Nette\Utils\FileSystem;

class UploadService extends Object
{
	/** @var string */
	private $rootFolder;

	/** @var string */
	private $cvsFolder;

	/** @var string */
	private $documentsFolder;

	/** @var string */
	private $url;


	public function uploadCv(FileUpload $file, $userId)
	{
		$dateTime = new DateTime();
		$uploadName = $file->getSanitizedName();
		$fileName = sprintf('%s_%s', $dateTime->getTimestamp(), $uploadName);
		$path = Helpers::getPath($this->rootFolder, $this->cvsFolder, $userId, $fileName);
		$file->move($path);
		return $path;
	}

	public function deleteCv($path)
	{
		if (file_exists($path)) {
			unlink($path);
		}
	}

	public function uploadDocument(FileUpload $file) {
		$dateTime = new DateTime();
		$uploadName = $file->getSanitizedName();
		$fileName = sprintf('%s_%s', $dateTime->getTimestamp(), $uploadName);
		$path = Helpers::getPath($this->rootFolder, $this->documentsFolder, $fileName);
		$file->move($path);
		return $fileName;
	}

	public function deleteDocument($fileName) {
		$path = Helpers::getPath($this->rootFolder, $this->documentsFolder, $fileName);
		if(file_exists($path)) {
			unlink($path);
		}
	}

	public function applyWebPath(Document $document) {
		$path = Helpers::getPath($this->url, $this->documentsFolder, $document->name);
		$document->setWebPath($path);
	}

	public function setFolders($config)
	{
		$this->rootFolder = $config['root_folder'];
		FileSystem::createDir($this->rootFolder);

		$this->cvsFolder = $config['cvs_folder'];
		$folder = Helpers::getPath($this->rootFolder, $this->cvsFolder);
		FileSystem::createDir($folder);

		$this->documentsFolder = $config['documents_folder'];
		$folder = Helpers::getPath($this->rootFolder, $this->documentsFolder);
		FileSystem::createDir($folder);
		return $this;
	}

	public function setUrl($url)
	{
		$this->url = $url;
		return $url;
	}
}