<?php

namespace App\AppModule\Presenters;

use App\Components\User\IUserControlFactory;
use App\Components\User\UserControl;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\TaggedString;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;

/**
 * Users presenter.
 */
class UsersPresenter extends BasePresenter
{
	// <editor-fold defaultstate="collapsed" desc="constants & variables">

	/** @var EntityManager @inject */
	public $em;

	/** @var EntityDao */
	private $userDao;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var IUserControlFactory @inject */
	public $iUserControlFactory;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->userDao = $this->em->getDao(User::getClassName());
	}

	// <editor-fold defaultstate="expanded" desc="actions & renderers">
	/**
	 * @secured
	 * @resource('users')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		$this->template->users = $this->userDao->findAll();
		$this->template->identityId = $this->getUser()->getId();
		$this->template->identityLowerRoles = $this->roleFacade->findLowerRoles($this->getUser()->getRoles());
	}

	/**
	 * @secured
	 * @resource('users')
	 * @privilege('add')
	 */
	public function actionAdd()
	{
		$this->setView('edit');
		$this->template->isAdd = TRUE;
	}

	/**
	 * @secured
	 * @resource('users')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$user = $this->userDao->find($id);
		if ($user) {
			$this['userForm']->setUser($user);
		} else {
			$this->flashMessage('This user wasn\'t found.', 'error');
			$this->redirect('default');
		}
	}

	/**
	 * @secured
	 * @resource('users')
	 * @privilege('view')
	 */
	public function actionView($id)
	{
		$this->flashMessage('Not implemented yet.', 'warning');
		$this->redirect('default');
	}

	/**
	 * @secured
	 * @resource('users')
	 * @privilege('delete')
	 */
	public function actionDelete($id)
	{
		if ($this->getUser()->getId() === $id) {
			$this->flashMessage('You can\'t delete yourself.', 'warning');
		} else {
			$user = $this->userDao->find($id);
			if ($user) {
				$this->userFacade->delete($user);
				$this->flashMessage('Entity was deleted.', 'success');
			} else {
				$this->flashMessage('Entity was not found.', 'warning');
			}
		}
		$this->redirect('default');
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="forms">

	/** @return UserControl */
	public function createComponentUserForm()
	{
		$control = $this->iUserControlFactory->create();
		$control->setIdentityRoles($this->getUser()->getRoles());
		$control->onAfterSave = function (User $savedUser) {
			$message = new TaggedString('User \'<%mail%>\' was successfully saved.', ['mail' => $savedUser->mail]);
			$this->flashMessage($message, 'success');
			$this->redirect('default');
		};
		return $control;
	}

	// </editor-fold>
}
