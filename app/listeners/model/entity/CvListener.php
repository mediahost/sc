<?php

namespace App\Listeners\Model\Entity;

use App\Extensions\CvToPdf;
use App\Model\Entity\Cv;
use Doctrine\ORM\Events;
use Kdyby\Events\Subscriber;
use Nette\Object;

class CvListener extends Object implements Subscriber
{

	/** @var CvToPdf @inject */
	public $cvTopdf;

	public function getSubscribedEvents()
	{
		return array(
			Events::prePersist,
			Events::preUpdate,
			Events::postRemove,
		);
	}

	// <editor-fold defaultstate="collapsed" desc="listeners redirectors">

	public function prePersist($params)
	{
		$this->savePdf($params);
	}

	public function preUpdate($params)
	{
		$this->savePdf($params);
	}

	public function postRemove($params)
	{
		$this->removePdf($params);
	}

	// </editor-fold>

	private function savePdf(Cv $cv)
	{
		$this->cvTopdf->save($cv);
	}

	private function removePdf(Cv $cv)
	{
		
	}

}
