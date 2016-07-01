<?php

namespace App\AppModule\Presenters;

use App\Components\User\IUserDataViewFactory;
use App\Components\User\IUserControlFactory;
use App\Components\User\UserControl;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\TaggedString;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Security\User as IdentityUser;

class UsersPresenter extends BasePresenter
{

	/** @var User */
	private $userEntity;

	/** @var EntityDao */
	private $userDao;

	// <editor-fold desc="injects">

	/** @var EntityManager @inject */
	public $em;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var IUserControlFactory @inject */
	public $iUserControlFactory;

	/** @var IUserDataViewFactory @inject */
	public $userDataViewFactory;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->userDao = $this->em->getDao(User::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('users')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		$this->template->users = $this->userDao->findAll();
		$this->template->identity = $this->user;
		$this->template->addFilter('canEdit', $this->userFacade->canEdit);
		$this->template->addFilter('canDelete', $this->userFacade->canDelete);
		$this->template->addFilter('canAccess', $this->userFacade->canAccess);
	}

	/**
	 * @secured
	 * @resource('users')
	 * @privilege('add')
	 */
	public function actionAdd()
	{
		$this->userEntity = new User;
		$this['userForm']->setUser($this->userEntity);
		$this->setView('edit');
	}

	/**
	 * @secured
	 * @resource('users')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$this->userEntity = $this->userDao->find($id);
		if (!$this->userEntity) {
			$this->flashMessage('This user wasn\'t found.', 'error');
			$this->redirect('default');
		} else if (!$this->userFacade->canEdit($this->user, $this->userEntity)) {
			$this->flashMessage('You can\'t edit this user.', 'danger');
			$this->redirect('default');
		} else {
			$this['userForm']->setUser($this->userEntity);
		}
	}

	public function renderEdit()
	{
		$this->template->isAdd = $this->userEntity->isNew();
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
		$user = $this->userDao->find($id);
		if (!$user) {
			$this->flashMessage('User wasn\'t found.', 'danger');
		} else if (!$this->userFacade->canDelete($this->user, $user)) {
			$this->flashMessage('You can\'t delete this user.', 'danger');
		} else {
			$this->userFacade->delete($user);
			$this->flashMessage('User was deleted.', 'success');
		}
		$this->redirect('default');
	}

	/**
	 * @secured
	 * @resource('users')
	 * @privilege('access')
	 */
	public function actionAccess($id, $view='Dashboard:')
	{
		$user = $this->userDao->find($id);
		if (!$user) {
			$this->flashMessage('User wasn\'t found.', 'danger');
		} else if (!$this->userFacade->canAccess($this->user, $user)) {
			$this->flashMessage('You can\'t access to this user.', 'danger');
		} else {
			$this->user->login($user);
			$message = new TaggedString('You are logged as \'%s\'.', $user);
			$this->flashMessage($message, 'success');
			$this->redirect($view);
		}
		$this->redirect('default');
	}
	

	// </editor-fold>
	// <editor-fold desc="forms">

	/** @return UserControl */
	public function createComponentUserForm()
	{
		$control = $this->iUserControlFactory->create();
		$control->setIdentityRoles($this->user->roles);
		$control->onAfterSave = function (User $savedUser) {
			$message = new TaggedString('User \'%s\' was successfully saved.', (string) $savedUser);
			$this->flashMessage($message, 'success');
			$this->redirect('default');
		};
		return $control;
	}

	/** @return \App\Components\User\UserDataView */
	public function createComponentUserDataView()
	{
		$control = $this->userDataViewFactory->create();
		$control->setUsers($this->userDao->findAll());
		$control->setIdentity($this->user);
		return $control;
	}
}
