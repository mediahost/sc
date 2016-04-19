<?php

namespace App\Components\User;

use App\Components\BaseControl;
use App\Model\Entity\User;

/**
 * Class UserDataView
 * @package App\Components\User
 */
class UserDataView extends BaseControl
{
    /** @var ArrayCollection */
    private $users;

    /** @var User */
    private $identity;


    /**
     * @inheritdoc
     */
    public function render()
    {
        $this->users = ($this->users)  ?  $this->users  :  new ArrayCollection();
        $this->template->users = $this->users;
        $this->template->identity = $this->identity;
        $this->template->addFilter('canEdit', $this->presenter->canEdit);
        $this->template->addFilter('canDelete', $this->presenter->canDelete);
        $this->template->addFilter('canAccess', $this->presenter->canAccess);
        parent::render();
    }

    /**
     * @param Array $users
     * @return $this
     */
    public function setUsers( $users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @param User $identity
     * @return $this
     */
    public function setIdentity(\Nette\Security\User $identity)
    {
        $this->identity = $identity;
        return $this;
    }
}


/**
 * Interface IUserDataViewFactory
 * @package App\Components\User
 */
interface IUserDataViewFactory
{
    /** @return UserDataView */
    public function create();
}