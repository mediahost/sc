<?php

namespace App\FrontModule\Presenters;

use Nette;
use Tracy\Debugger as Debug;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

    public function actionDefault()
    {
        $this->redirect(":Admin:Dashboard:");
    }

}
