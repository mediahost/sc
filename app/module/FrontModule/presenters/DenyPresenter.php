<?php

namespace App\FrontModule\Presenters;

use Nette;
use Tracy\Debugger as Debug;

/**
 * Deny presenter.
 */
class DenyPresenter extends BasePresenter
{

    public function actionDefault()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect("Sign:");
        }
        $this->setLayout("metronic.layout");
    }

}
