<?php

namespace App\Listeners\Model\Entity;

use App\Model\Entity\Cv;
use Doctrine\ORM\Events;
use Kdyby\Events\Subscriber;
use Nette\Object;

/**
 * Inicializovaný listener, bez využití
 */
class CvListener extends Object implements Subscriber
{

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
		
	}

	private function removePdf(Cv $cv)
	{
		
	}

}
