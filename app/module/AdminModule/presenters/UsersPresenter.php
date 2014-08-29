<?php

namespace App\AdminModule\Presenters;

use Nette;
use Tracy\Debugger as Debug;
use App\Model\Entity;

/**
 * Users presenter.
 */
class UsersPresenter extends BasePresenter
{

	/** @var \Kdyby\Doctrine\EntityManager @inject */
	public $em;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $userDao;

	/** @var \App\Model\Facade\UserFacade @inject */
	public $userFacade;

	/** @var array */
	public $users;

	/** @var \App\Forms\UserFormFactory @inject */
	public $userFormFactory;

	/** @var \App\Model\Entity\User */
	private $user;

	protected function startup()
	{
		parent::startup();
		$this->userDao = $this->em->getDao(Entity\User::getClassName());
	}

	/**
	 * @secured
	 * @resource('admin')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		$this->users = $this->userDao->findAll();
	}

	public function renderDefault()
	{
		$this->template->users = $this->users;
	}

	/**
	 * @secured
	 * @resource('admin')
	 * @privilege('add')
	 */
	public function actionAdd()
	{
		$this->user = new \App\Model\Entity\User;
		$this->userFormFactory->setAdding();
		$this->setView("edit");
	}

	/**
	 * @secured
	 * @resource('admin')
	 * @privilege('edit')
	 */
	public function actionEdit($id)
	{
		$this->user = $this->userDao->find($id);
	}

	public function renderEdit()
	{
		$this->template->isAdd = $this->userFormFactory->isAdding();
	}

	/**
	 * @secured
	 * @resource('admin')
	 * @privilege('view')
	 */
	public function actionView($id)
	{
		$this->flashMessage("Not implemented yet.", 'warning');
		$this->redirect("default");
	}

	/**
	 * @secured
	 * @resource('admin')
	 * @privilege('delete')
	 */
	public function actionDelete($id)
	{
		$this->user = $this->userDao->find($id);
		if ($this->user) {
			$this->userFacade->delete($this->user);
			$this->flashMessage("Entity was deleted.", 'success');
		} else {
			$this->flashMessage("Entity was not found.", 'warning');
		}
		$this->redirect("default");
	}

// <editor-fold defaultstate="collapsed" desc="Forms">

	public function createComponentUserForm()
	{
		$form = $this->formFactoryFactory
				->create($this->userFormFactory)
				->setEntity($this->user)
				->create();
		$form->onSuccess[] = $this->userFormSuccess;
		return $form;
	}

	public function userFormSuccess($form)
	{
		if ($form['submitContinue']->submittedBy) {
			$this->userDao->save($this->user);
			$this->redirect("edit", $this->user->getId());
		}
		$this->redirect("Users:");
	}

// </editor-fold>
}
