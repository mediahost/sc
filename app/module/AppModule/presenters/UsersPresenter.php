<?php

namespace App\AppModule\Presenters;

use App\Components\Grids\User\IUsersGridFactory;
use App\Components\Grids\User\UsersGrid;
use App\Components\User;
use App\Model\Entity;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;

class UsersPresenter extends BasePresenter
{

	/** @var Entity\User */
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

	/** @var User\IUserFactory @inject */
	public $iUserFactory;

	/** @var IUsersGridFactory @inject */
	public $iUsersGridFactory;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->userDao = $this->em->getDao(Entity\User::getClassName());
	}

	// <editor-fold desc="actions & renderers">

	/**
	 * @secured
	 * @resource('users')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		if ($this->company) {
			$this['usersGrid']->setCompany($this->company, TRUE);
		}
	}

	/**
	 * @secured
	 * @resource('users')
	 * @privilege('addCandidate')
	 */
	public function actionAddCandidate()
	{
		$this->_actionAddUser(Entity\Role::CANDIDATE);
	}

	/**
	 * @secured
	 * @resource('users')
	 * @privilege('addCompany')
	 */
	public function actionAddCompany()
	{
		$this->_actionAddUser(Entity\Role::COMPANY, TRUE);
	}

	/**
	 * @secured
	 * @resource('users')
	 * @privilege('addAdmin')
	 */
	public function actionAddAdmin()
	{
		$this->_actionAddUser(Entity\Role::ADMIN);
	}

	private function _actionAddUser($roleName, $company = FALSE)
	{
		$this->userEntity = new Entity\User(NULL, TRUE);

		$roleRepo = $this->em->getRepository(Entity\Role::getClassName());
		$role = $roleRepo->findOneByName($roleName);
		$this->userEntity->addRole($role);

		/** @var User\User $form */
		$form = $this['userForm'];
		$form->setUser($this->userEntity);
		if ($company && $this->company) {
			$form->setCompany($this->company);
		}

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
			$message = $this->translator->translate('This user wasn\'t found.');
			$this->flashMessage($message, 'error');
			$this->redirect('default');
		} else if (!$this->userFacade->canEdit($this->user, $this->userEntity)) {
			$message = $this->translator->translate('You can\'t edit this user.');
			$this->flashMessage($message, 'danger');
			$this->redirect('default');
		} else {
			$this['userForm']->setUser($this->userEntity);
		}
	}

	public function renderEdit()
	{
		$this->template->userEntity = $this->userEntity;
	}

	/**
	 * @secured
	 * @resource('users')
	 * @privilege('view')
	 */
	public function actionView($id)
	{
		$message = $this->translator->translate('Not implemented yet.');
		$this->flashMessage($message, 'warning');
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
			$message = $this->translator->translate('User wasn\'t found.');
			$this->flashMessage($message, 'danger');
		} else if (!$this->userFacade->canDelete($this->user, $user)) {
			$message = $this->translator->translate('You can\'t delete this user.');
			$this->flashMessage($message, 'danger');
		} else {
			$this->userFacade->delete($user);
			$message = $this->translator->translate('User was deleted.');
			$this->flashMessage($message, 'success');
		}
		$this->redirect('default');
	}

	/**
	 * @secured
	 * @resource('users')
	 * @privilege('access')
	 */
	public function actionAccess($id, $view = 'Dashboard:')
	{
		$user = $this->userDao->find($id);
		if (!$user) {
			$message = $this->translator->translate('User wasn\'t found.');
			$this->flashMessage($message, 'danger');
		} else if (!$this->userFacade->canAccess($this->user, $user)) {
			$message = $this->translator->translate('You can\'t access to this user.');
			$this->flashMessage($message, 'danger');
		} else {
			$this->user->login($user);
			$message = $this->translator->translate('You are logged as \'%user%\'.', ['user' => $user]);
			$this->flashMessage($message, 'success');
			$this->redirect($view);
		}
		$this->redirect('default');
	}


	// </editor-fold>
	// <editor-fold desc="forms">

	/** @return User\User */
	public function createComponentUserForm()
	{
		$control = $this->iUserFactory->create();
		$control->onAfterSave = function (Entity\User $savedUser, $action) {
			$message = $this->translator->translate('User \'%user%\' was successfully saved.', ['user' => (string)$savedUser]);
			$this->flashMessage($message, 'success');
			if ($action && $action == 'goToItSkills' && $savedUser->isCandidate()) {
				$this->redirect('CvEditor:skills', $savedUser->person->candidate->cv->id);
			} elseif ($action && $action == 'goToCandidate' && $savedUser->isCandidate()) {
				$this->redirect('Profile:', $savedUser->person->candidate->profileId);
			} else {
				$this->redirect('default');
			}
		};
		return $control;
	}

	/** @return UsersGrid */
	public function createComponentUsersGrid()
	{
		$control = $this->iUsersGridFactory->create();
		return $control;
	}
}
