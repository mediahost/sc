<?php

namespace App\AppModule\Presenters;

use App\BaseModule\Presenters\BasePresenter as BaseBasePresenter;
use App\Model\Entity\Role;

abstract class BasePresenter extends BaseBasePresenter
{

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->isCandidate = in_array(Role::ROLE_CANDIDATE, $this->getUser()->getRoles());
		$this->template->isCompany = in_array(Role::ROLE_COMPANY, $this->getUser()->getRoles());
		$this->template->isAdmin = in_array(Role::ROLE_ADMIN, $this->getUser()->getRoles());
	}

}
