<?php

namespace App\AdminModule\Presenters;

use Nette;
use Tracy\Debugger as Debug;

/**
 * Users presenter.
 */
class UsersPresenter extends BasePresenter
{

    /** @var \App\Model\Facade\Users @inject */
    public $userFacade;

    /** @var array */
    public $users;

    /** @var \App\Forms\UserFormFactory @inject */
    public $userFormFactory;

    /** @var \App\Model\Entity\User */
    private $user;



	/**
	 * @secured
	 * @resource('users')
	 * @privilege('default')
	 */
    public function actionDefault()
    {
        $this->users = $this->userFacade->findAll();
    }

    public function renderDefault()
    {
        $this->template->users = $this->users;
    }

	/**
	 * @secured
	 * @resource('users')
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
	 * @resource('users')
	 * @privilege('edit')
	 */
    public function actionEdit($id)
    {
        $this->user = $this->userFacade->find($id);
    }

    public function renderEdit()
    {
        $this->template->isAdd = $this->userFormFactory->isAdding();
    }


	/**
	 * @secured
	 * @resource('users')
	 * @privilege('view')
	 */
    public function actionView($id)
    {
        $this->flashMessage("Not implemented yet.", 'warning');
        $this->redirect("default");
    }


	/**
	 * @secured
	 * @resource('users')
	 * @privilege('delete')
	 */
    public function actionDelete($id)
    {
        $this->user = $this->userFacade->find($id);
        if ($this->user) {
            if (!$this->user->getProjectsCount()) {
                $this->userFacade->delete($this->user);
                $this->flashMessage("Entity was deleted.", 'success');
            } else {
                $this->flashMessage("User cannot be deleted. Remove roles first.", 'warning');
            }
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
            $this->userFacade->save($this->user);
            $this->redirect("edit", $this->user->getId());
        }
        $this->redirect("Users:");
    }

// </editor-fold>
}
