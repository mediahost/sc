<?php

namespace App\Components\User;
use Nette\Http\FileUpload;
use Nette\Object;
use Nette\Utils\DateTime;


class DocStorage extends Object
{
    const DOC_DIR = 'careerDocs';

    /** @var string */
    private $dir;


    /**
     * DocStorage constructor.
     * @param $dir
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * @param FileUpload $file
     * @param $fileName
     * @return FileUpload|static
     * @throws CareerDocsException
     */
    public function upload(FileUpload $file, &$fileName)
    {
        if (!$file->isOk()) {
            throw new CareerDocsException('Document is not uploaded');
        }

        $dateTime = new DateTime();
        $uploadName = $file->getSanitizedName();
        $fileName = sprintf('%s_%s', $dateTime->getTimestamp(), $uploadName);
        $path = sprintf('%s/%s/%s', $this->dir, self::DOC_DIR, $fileName);
        $file = $file->move($path);
        return $file;
    }
    
    public function removeFile($fileName) {
        $path = sprintf('%s/%s/%s', $this->dir, self::DOC_DIR, $fileName);
        if(file_exists($path)) {
            unlink($path);
        }
    }

    public static function getDisplayName($name) {
        $displayName = '';
        sscanf($name, "%d_%s", $timestamp, $displayName);
        return $displayName;
    }
}