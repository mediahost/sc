<?php

namespace App\AdminModule\Presenters;

use Nette;
use Tracy\Debugger as Debug;

/**
 * Companies presenter.
 */
class CompaniesPresenter extends BasePresenter
{

    /** @var \App\Model\Facade\CompanyFacade @inject */
    public $companyFacade;

    /** @var array */
    public $companies;

    /** @var \App\Forms\CompanyFormFactory @inject */
    public $companyFormFactory;

    /** @var \App\Model\Entity\Company */
    private $company;

    protected function startup()
    {
        parent::startup();
        $this->isAllowed("companies", "view");
    }

    public function actionDefault()
    {
        $this->companies = $this->companyFacade->findAll();
    }

    public function renderDefault()
    {
        $this->template->companies = $this->companies;
    }

    public function actionAdd()
    {
        $this->company = new \App\Model\Entity\Company;
        $this->companyFormFactory->setAdding();
        $this->setView("edit");
    }

    public function actionEdit($id)
    {
        $this->company = $this->companyFacade->find($id);
    }

    public function renderEdit()
    {
        $this->template->isAdd = $this->companyFormFactory->isAdding();
    }

    public function actionView($id)
    {
        $this->flashMessage("Not implemented yet.", 'warning');
        $this->redirect("default");
    }

    public function actionDelete($id)
    {
        $this->company = $this->companyFacade->find($id);
        if ($this->company) {
            if (!$this->company->getProjectsCount()) {
                $this->companyFacade->delete($this->company);
                $this->flashMessage("Entity was deleted.", 'success');
            } else {
                $this->flashMessage("Company cannot be deleted. Remove projects first.", 'warning');
            }
        } else {
            $this->flashMessage("Entity was not found.", 'warning');
        }
        $this->redirect("default");
    }

// <editor-fold defaultstate="collapsed" desc="Forms">

    public function createComponentCompanyForm()
    {
        $form = $this->formFactoryFactory
                ->create($this->companyFormFactory)
                ->setEntity($this->company)
                ->create();
        $form->onSuccess[] = $this->companyFormSuccess;
        return $form;
    }

    public function companyFormSuccess($form)
    {
        if ($form['submitContinue']->submittedBy) {
            $this->companyFacade->save($this->company);
            $this->redirect("edit", $this->company->getId());
        }
        $this->redirect("Companies:");
    }

// </editor-fold>
}
