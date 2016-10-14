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
	private $url;

	public function uploadFile(FileUpload $file) {
		$dateTime = new DateTime();
		$uploadName = $file->getSanitizedName();
		$fileName = sprintf('%s_%s', $dateTime->getTimestamp(), $uploadName);
		$path = Helpers::getPath($this->rootFolder, $fileName);
		$file->move($path);
		return $fileName;
	}

	public function deleteFile($fileName) {
		$path = Helpers::getPath($this->rootFolder, $fileName);
		if(file_exists($path)) {
			unlink($path);
		}
	}

	public function setFolder($folder)
	{
		$this->rootFolder = $folder;
		FileSystem::createDir($folder);
		return $this;
	}

	public function setUrl($url)
	{
		$this->url = $url;
		return $url;
	}
}