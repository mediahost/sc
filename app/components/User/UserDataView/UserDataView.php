<?php

namespace App\Components\User;

use App\Components\BaseControl;
use App\Model\Facade\UserFacade;
use App\Model\Entity\User;

class UserDataView extends BaseControl
{
	/** @var UserFacade @inject */
	public $userFacade;

	/** @var ArrayCollection */
	private $users;

	/** @var User */
	private $identity;

	public function render()
	{
		$this->users = ($this->users) ? $this->users : new ArrayCollection();
		$this->template->users = $this->users;
		$this->template->identity = $this->identity;
		$this->template->addFilter('canEdit', $this->userFacade->canEdit);
		$this->template->addFilter('canDelete', $this->userFacade->canDelete);
		$this->template->addFilter('canAccess', $this->userFacade->canAccess);
		parent::render();
	}

	public function setUsers($users)
	{
		$this->users = $users;
		return $this;
	}

	public function setIdentity(\Nette\Security\User $identity)
	{
		$this->identity = $identity;
		return $this;
	}
}

interface IUserDataViewFactory
{
	/** @return UserDataView */
	public function create();
}