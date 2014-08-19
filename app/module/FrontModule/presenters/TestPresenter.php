<?php

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
	
	/** @var \App\Model\Facade\Roles @inject */
	public $roles;
	
	/** @var \App\Model\Facade\Users @inject */
	public $users;	
	
	public function actionDefault()
	{
		$user = new \App\Model\Entity\User();
		$user->email = 'test@test.cz';
		
		$this->users->addRole($user, ['superadmin', 'guest', 'kokot']);
		
//		$this->em->persist($user);
//		$this->em->flush();
	}
}
