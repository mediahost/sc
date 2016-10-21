<?php

namespace App\Listeners\Model\Entity;

use App\Extensions\UploadService;
use App\Model\Entity\Candidate;
use Doctrine\ORM\Events;
use Kdyby\Events\Subscriber;
use Nette\Http\FileUpload;
use Nette\Object;

class CandidateListener extends Object implements Subscriber
{
	/** @var UploadService @inject */
	public $uploadService;

	public function getSubscribedEvents()
	{
		return array(
			Events::prePersist,
			Events::preUpdate,
			Events::postRemove,
		);
	}

	public function prePersist($params) {
		$this->uploadFile($params);
	}

	public function preUpdate($params)
	{
		$this->uploadFile($params);
	}

	public function postRemove($params) {
		$this->deleteFile($params);
	}

	private function uploadFile(Candidate $candidate) {
		if ($candidate->cvFile instanceof FileUpload) {
			$userId = $candidate->person->user->id;
			$fileName = $this->uploadService->uploadCv($candidate->cvFile, $userId);
			$candidate->cvFile = $fileName;
		}
	}

	private function deleteFile(Candidate $candidate) {
		$this->uploadService->deleteCv($candidate->cvFile);
	}
}