<?php

namespace App\Extensions;

use App\Helpers;
use Exception;
use Nette\Http\FileUpload;
use Nette\Object;
use Nette\Utils\Image;
use Tracy\Debugger;

/**
 * Foto
 *
 * @author Petr Poupě
 */
class Foto extends Object
{
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var string */
	private $rootFolder;

	/** @var string */
	private $originalFolder;

	/** @var string */
	private $defaultFilename;

	/** @var string */
	private $defaultFormat;

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="setters">

	/** @return self */
	public function setFolders($folder, $originalFolderName)
	{
		$this->rootFolder = $folder;
		Helpers::mkDir($folder);
		if (!is_dir($folder)) {
			throw new FotoException('Folder \'' . $folder . '\' cannot create.');
		}

		$this->originalFolder = Helpers::getPath($folder, $originalFolderName);
		Helpers::mkDir($this->originalFolder);
		if (!is_dir($this->originalFolder)) {
			throw new FotoException('Folder \'' . $this->originalFolder . '\' cannot create.');
		}

		return $this;
	}

	/** @return self */
	public function setDefaultImage($defaultFilename, $defaultFormat = NULL)
	{
		if (!$defaultFilename) {
			throw new FotoException('Default filename must be set.');
		}
		if (!is_file(Helpers::getPath($this->originalFolder, $defaultFilename))) {
			throw new FotoException('Default filename must exist in original folder \'' . $this->originalFolder . '\'.');
		}
		$this->defaultFilename = $defaultFilename;
		switch ($defaultFormat) {
			case 'jpg':
				$this->defaultFormat = Image::JPEG;
				break;
			case 'gif':
				$this->defaultFormat = Image::GIF;
				break;
			case 'png':
			default:
				$this->defaultFormat = Image::PNG;
				break;
		}

		return $this;
	}

	// </editor-fold>

	/**
	 * Display requested image
	 * @param string $name
	 * @param string $size in format 'width-height'
	 * @return type
	 */
	public function display($name = NULL, $size = NULL)
	{
		$filename = Helpers::getPath($this->originalFolder, $name);

		if (empty($name) || !is_file($filename)) {
			$name = $this->defaultFilename;
			$filename = Helpers::getPath($this->originalFolder, $name);
		}

		$sizeX = 0;
		$sizeY = 0;
		if (preg_match("@^(\d+)\-(\d+)$@", $size, $matches)) {
			$sizeX = $matches[1];
			$sizeY = $matches[2];
		}

		if ($sizeX > 0 && $sizeY > 0) {
			$resizedPath = Helpers::getPath($this->originalFolder, "{$sizeX}-{$sizeY}");
			Helpers::mkDirForce(Helpers::getPath($resizedPath, self::getFolderFromPath($name)));
			$resized = Helpers::getPath($resizedPath, $name);

			if (!file_exists($resized) || filemtime($filename) > filemtime($resized)) {
				$img = Image::fromFile($filename);

				$sizeX = $sizeX < $img->width ? $sizeX : $img->width;
				$sizeY = $sizeY < $img->height ? $sizeY : $img->height;

				$img->resize($sizeX, $sizeY);
				$img->save($resized);
			}

			$filename = $resized;
		}

		try {
			$finishImage = Image::fromFile($filename);
			$recognizedType = self::recognizeTypeFromName($filename);
			if ($recognizedType) {
				$finishImage->send($recognizedType);
			} else {
				$finishImage->send($this->defaultFormat);
			}
		} catch (\Exception $ex) {
			Debugger::log($ex->getMessage(), 'image');
		}
		return NULL;
	}

	/**
	 * Save image and return used filename
	 * @param FileUpload|string $source
	 * @param type $filename filename for save
	 * @param type $folder set added folder to save image (for ex. products)
	 * @param type $format
	 * @return type filename
	 * @throws FotoException
	 */
	public static function create($source, $filename, $folder = NULL, $format = Image::PNG)
	{
		if ($source instanceof FileUpload) { // uploaded
			$img = Image::fromString($source->contents);
		} else if (is_string($source)) { // filename or string
			$img = file_exists($source) ? Image::fromFile($source) : Image::fromString($source);
		} else {
			throw new FotoException("This source format isn't supported");
		}

		switch ($format) {
			case Image::JPEG:
				$ext = "jpg";
				break;
			case Image::PNG:
				$ext = "png";
				break;
			default:
				throw new FotoException("This requested format isn't supported");
		}
		$filename .= ".{$ext}";

		$folderFullPath = Helpers::getPath($this->originalFolder, $folder);
		Helpers::mkDirForce($folderFullPath);

		$this->delete(Helpers::getPath($folder, $filename));

		$fullFilename = self::getPath($folderFullPath, $filename);
		$img->save($fullFilename);

		return $fullFilename;
	}

	/**
	 * Delete origin and all resized images
	 * @param string $name
	 */
	public function delete($name, $deleteResized = TRUE)
	{
		$filename = Helpers::getPath($this->originalFolder, $name);
		$this->deleteFile($filename);
		if ($deleteResized) {
			$this->deleteResized($name);
		}
	}

	/**
	 * Delete resized files by origin name
	 * @param string $name
	 */
	public function deleteResized($name)
	{
		foreach (scandir($this->rootFolder) as $dir) {
			if (preg_match("@^\d+\-\d+$@", $dir)) {
				$filename = Helpers::getPath($this->originalFolder, $dir, $name);
				$this->deleteFile($filename);
			}
		}
	}

	// <editor-fold defaultstate="collapsed" desc="helpers">

	/**
	 * Delete file or log it
	 * @param string $filename
	 * @return boolean
	 */
	private function deleteFile($filename)
	{
		if ($filename && file_exists($filename) && !@unlink($filename)) {
			Debugger::log($filename . ' wasn\'t deleted.', 'image');
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * Returns folder form path
	 * @param string $path
	 * @return string
	 */
	public static function getFolderFromPath($path)
	{
		$splited = preg_split('~/~', $path, -1, PREG_SPLIT_NO_EMPTY);
		if (count($splited) > 1) {
			array_pop($splited);
			return Helpers::getPath($splited);
		}
		return NULL;
	}

	/**
	 * Return recognized type from file extension
	 * @param string $filename
	 * @return string
	 */
	public static function recognizeTypeFromName($filename)
	{
		if (preg_match('~\.(png|jpg|jpeg|gif)$~i', $filename, $matches)) {
			switch ($filename) {
				case 'jpeg':
				case 'jpg':
					return Image::JPEG;
				case 'png':
					return Image::PNG;
				case 'gif':
					return Image::GIF;
			}
		}
		return NULL;
	}

	// </editor-fold>
}

class FotoException extends Exception
{
	
}
