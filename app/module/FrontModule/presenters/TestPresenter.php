<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\FrontModule\Presenters;

/**
 * Description of TestPresenter
 *
 * @author Martin Šifra <me@martinsifra.cz>
 */
class TestPresenter extends BasePresenter
{
	/** @var \Kdyby\Doctrine\EntityManager @inject */
	public $em;
	
	public function actionDefault()
	{
		$user = new \App\Model\Entity\User();
		$user->setEmail('kokos@blecha.cz');
		
		$auth1 = new \App\Model\Entity\Auth();
		$auth1->setSource('testing1')
				->setKey(\Nette\Utils\Random::generate(32));
		
		$auth2 = new \App\Model\Entity\Auth();
		$auth2->setSource('testing1')
				->setKey(\Nette\Utils\Random::generate(32));
		
		$user->addAuth($auth1);
		$user->addAuth($auth2);
		
		$this->em->persist($user);
		$this->em->flush();
	}
}
