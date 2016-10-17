<?php

namespace App\Extensions;

use App\Helpers;
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

	public function uploadFile(FileUpload $file)
	{
		$dateTime = new DateTime();
		$uploadName = $file->getSanitizedName();
		$fileName = sprintf('%s_%s', $dateTime->getTimestamp(), $uploadName);
		$path = Helpers::getPath($this->rootFolder, $fileName);
		$file->move($path);
		return $fileName;
	}

	public function deleteFile($fileName)
	{
		$path = Helpers::getPath($this->rootFolder, $fileName);
		if (file_exists($path)) {
			unlink($path);
		}
	}

	public function uploadCv(FileUpload $file, $userId)
	{
		$dateTime = new DateTime();
		$uploadName = $file->getSanitizedName();
		$fileName = sprintf('%s_%s', $dateTime->getTimestamp(), $uploadName);
		$path = Helpers::getPath($this->rootFolder, $this->cvsFolder, $userId, $fileName);
		$file->move($path);
		return $fileName;
	}

	public function deleteCv($fileName, $userId)
	{
		$path = Helpers::getPath($this->rootFolder, $this->cvsFolder, $userId, $fileName);
		if (file_exists($path)) {
			unlink($path);
		}
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