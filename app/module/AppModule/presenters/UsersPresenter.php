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
use Nette\Security\User as IdentityUser;

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
		$this->template->identity = $this->user;
		$this->template->addFilter('canEdit', $this->canEdit);
		$this->template->addFilter('canDelete', $this->canDelete);
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
		if (!$user) {
			$this->flashMessage('This user wasn\'t found.', 'error');
			$this->redirect('default');
		} else if (!$this->canEdit($this->getUser(), $user)) {
			$this->flashMessage('You can\'t edit this user.', 'warning');
			$this->redirect('default');
		} else {
			$this['userForm']->setUser($user);
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
		$user = $this->userDao->find($id);
		if (!$user) {
			$this->flashMessage('User wasn\'t found.', 'warning');
		} else if (!$this->canDelete($this->getUser(), $user)) {
			$this->flashMessage('You can\'t delete this user.', 'warning');
		} else {
			$this->userDao->delete($user);
			$this->flashMessage('User was deleted.', 'success');
		}
		$this->redirect('default');
	}

	// </editor-fold>
	// <editor-fold defaultstate="expanded" desc="edit/delete priviledges">

	/**
	 * Decides if identity user can edit user
	 * @param IdentityUser $identityUser
	 * @param User $user
	 * @return boolean
	 */
	public function canEdit(IdentityUser $identityUser, User $user)
	{
		if ($identityUser->id === $user->id) {
			return FALSE;
		} else {
			// pokud je nejvyšší uživatelova role v nižších rolích přihlášeného uživatele
			// tedy může editovat pouze uživatele s nižšími rolemi
			$identityLowerRoles = $this->roleFacade->findLowerRoles($identityUser->roles);
			return in_array($user->maxRole->name, $identityLowerRoles);
		}
	}

	/**
	 * Decides if identity user can delete user
	 * @param IdentityUser $identityUser
	 * @param User $user
	 * @return boolean
	 */
	public function canDelete(IdentityUser $identityUser, User $user)
	{
		return $this->canEdit($identityUser, $user);
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
