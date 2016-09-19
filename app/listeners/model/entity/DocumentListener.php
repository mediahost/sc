<?php

namespace App\Listeners\Model\Entity;

use App\Extensions\DocumentService;
use App\Model\Entity\Document;
use Kdyby\Events\Subscriber;
use Nette\Object;

class DocumentListener extends Object implements Subscriber
{
	/** @var DocumentService @inject */
	public $documentService;

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
		$fileName = $this->documentService->uploadFile($document->file);
		$document->name = $fileName;
	}

	private function deleteFile(Document $document) {
		$this->documentService->deleteFile($document->name);
	}

	private function injectProperties(Document $document) {
		$this->documentService->applyWebPath($document);
	}
}