<?php

namespace App\AdminModule\Presenters;

use App\Components\Sign;

/**
 * User Settings presenter.
 */
class UserSettingsPresenter extends BasePresenter
{

	/** @var \App\Model\Facade\AuthFacade @inject */
	public $authFacade;

	/** @var Sign\IAuthControlFactory @inject */
	public $iAuthControlFactory;

	/** @var Sign\IDeleteControlFactory @inject */
	public $iDeleteControlFactory;
	
	/** @var \Kdyby\Doctrine\EntityDao */
	private $userDao;
	
	/** @var \App\Model\Facade\UserFacade @inject */
	private $userFacade;
	
	/**
	 * @secured
	 * @resource('user_settings')
	 * @privilege('view')
	 */
	public function actionDefault()
	{
		
	}

	/**
	 * @secured
	 * @resource('user_settings')
	 * @privilege('authorization')
	 */
	public function actionAuthorization()
	{
		$this['auth']->setForce();
	}

	/**
	 * @secured
	 * @resource('user_settings')
	 * @privilege('authorization')
	 */
	public function actionSetPassword()
	{
		$mail = $this->user->getIdentity()->mail;
		
		if ($this->authFacade->findByMail($mail)) {
			$this->redirect('authorization');
		}
	}
	
	/**
	 * @secured
	 * @resource('user_settings')
	 * @privilege('delete')
	 */
	public function actionDelete()
	{

	}
	
	public function injectEntityManager(\Kdyby\Doctrine\EntityManager $em)
	{
		$this->userDao = $em->getDao(\App\Model\Entity\User::getClassName());
	}
	
// <editor-fold defaultstate="collapsed" desc="Components">

	/** @return Sign\AuthControl */
	protected function createComponentAuth()
	{
		return $this->iAuthControlFactory->create();
	}
	
	/** @return Sign\DeleteControl */
	protected function createComponentDelete()
	{
		return $this->iDeleteControlFactory->create();
	}

// </editor-fold>
}
