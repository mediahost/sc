<?php

namespace App\Forms;

/**
 * CompanyFormFactory
 *
 * @author Petr Poupě
 */
class CompanyFormFactory extends FormFactory
{

    /** @var \App\Model\Facade\Users */
    private $userFacade;

    /** @var array<\App\Model\Entity\User> */
    private $users;

    public function __construct(IFormFactory $formFactory, \App\Model\Facade\Users $userFacade)
    {
        parent::__construct($formFactory);
        $this->userFacade = $userFacade;
    }

    private function getUsers()
    {
        if ($this->users === NULL) {
            $this->users = $this->userFacade->findPairs("username");
        }
        return $this->users;
    }

    public function create()
    {
        $form = $this->formFactory->create();
        $form->addText('name', 'Name')
                ->setRequired("Name must be filled")
                ->setAttribute("placeholder", "Company name");
        $form->addMultiSelect2("users", "Users", $this->getUsers());
        
        $form->addSubmit('_submit', 'Save');
        $form->addSubmit('submitContinue', 'Save and continue edit');
        return $form;
    }

}
