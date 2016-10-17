<?php

namespace App\Listeners\Model\Entity;

use App\Extensions\UploadService;
use App\Model\Entity\Document;
use Doctrine\ORM\Events;
use Kdyby\Events\Subscriber;
use Nette\Object;

class DocumentListener extends Object implements Subscriber
{
	/** @var UploadService @inject */
	public $uploadService;

	public function getSubscribedEvents()
	{
		return array(
			Events::postLoad,
			Events::prePersist,
			Events::postRemove,
		);
	}

	public function prePersist($params) {
		$this->uploadFile($params);
	}

	public function postRemove($params) {
		$this->deleteFile($params);
	}

	public function postLoad($params) {
		$this->injectProperties($params);
	}

	private function uploadFile(Document $document) {
		$fileName = $this->uploadService->uploadDocument($document->file);
		$document->name = $fileName;
	}

	private function deleteFile(Document $document) {
		$this->uploadService->deleteDocument($document->name);
	}

	private function injectProperties(Document $document) {
		$this->uploadService->applyWebPath($document);
	}
}