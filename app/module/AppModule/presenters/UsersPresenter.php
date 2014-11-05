<?php

namespace App\AppModule\Presenters;

use Nette;
use Tracy\Debugger as Debug;
use App\Model\Entity;

/**
 * Users presenter.
 */
class UsersPresenter extends BasePresenter
{
	// <editor-fold defaultstate="collapsed" desc="constants & variables">

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
	private $userEntity;

	// </editor-fold>

	protected function startup()
	{
		parent::startup();
		$this->userDao = $this->em->getDao(Entity\User::getClassName());
	}

	// <editor-fold defaultstate="collapsed" desc="actions & renderers">
	/**
	 * @secured
	 * @resource('users')
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
	 * @resource('users')
	 * @privilege('add')
	 */
	public function actionAdd()
	{
		$this->userEntity = new \App\Model\Entity\User;
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
		$this->userEntity = $this->userDao->find($id);
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
		$this->userEntity = $this->userDao->find($id);
		if ($this->userEntity) {
			$this->userFacade->delete($this->userEntity);
			$this->flashMessage("Entity was deleted.", 'success');
		} else {
			$this->flashMessage("Entity was not found.", 'warning');
		}
		$this->redirect("default");
	}

	// </editor-fold>
	// <editor-fold defaultstate="collapsed" desc="forms">

	public function createComponentUserForm()
	{
		$form = $this->formFactoryFactory
				->create($this->userFormFactory)
				->setEntity($this->userEntity)
				->create();
		
		$form->onSaveButton[] = function() {
			$this->redirect("Users:");
		};
		$form->onContinueButton[] = function () {
			$this->redirect("edit", $this->userEntity->getId());
		};
		
		return $form;
	}

	// </editor-fold>
}
