<?php

namespace App\Forms;

/**
 * ProjectFormFactory
 *
 * @author Petr Poupě
 */
class ProjectFormFactory extends FormFactory
{

    /** @var \App\Model\Facade\CompanyFacade */
    private $companyFacade;

    /** @var array */
    private $companies;

    public function __construct(IFormFactory $formFactory, \App\Model\Facade\CompanyFacade $companyFacade)
    {
        parent::__construct($formFactory);
        $this->companyFacade = $companyFacade;
    }

    private function getCompanies()
    {
        if ($this->companies === NULL) {
            $this->companies = $this->companyFacade->findPairs("name");
        }
        return $this->companies;
    }

    public function create()
    {
        $form = $this->formFactory->create();
        $form->addText('name', 'Name')
                ->setRequired("Name must be filled")
                ->setAttribute("placeholder", "Project name");
        $form->addSelect2("company", "Client", $this->getCompanies())
                ->setAttribute("placeholder", "Select company")
                ->setRequired("Client must be selected");
        
        $form->addSubmit('_submit', 'Save');
        $form->addSubmit('submitContinue', 'Save and continue edit');
        return $form;
    }

}
