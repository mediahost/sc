<?php

namespace App\Model\Permission;

/**
 * Permission model of access control list
 *
 * @author Petr Poupě
 */
class Permission extends \Nette\Security\Permission
{

    public function __construct()
    {
        // definujeme role
        $this->setRoles();
        // seznam zdrojů, ke kterým mohou uživatelé přistupovat
        $this->setResources();
        // pravidla, určující, kdo co může s čím dělat - defaultně vše zakázáno
        $this->setPrivileges();
    }

    private function setRoles()
    {
        $this->addRole('guest');
        $this->addRole('client', 'guest');
        $this->addRole('programmer', 'client');
        $this->addRole('manager', 'programmer');

        $this->addRole('admin', 'manager');
        $this->addRole('superadmin', 'admin');
    }

    private function setResources()
    {
        $this->addResource('front');
        
        $this->addResource('admin');
        $this->addResource('dashboard');
        $this->addResource('tasks');
        $this->addResource('comments'); 
        $this->addResource('projects'); 
        $this->addResource('companies');
        
        $this->addResource('users');

        $this->addResource('service');
    }

    private function setPrivileges()
    {
        /**
         * VIEW - view own data
         * VIEW-ALL - view all data
         * ADD - can add data
         * EDIT - can edit own data
         * EDIT-ALL - can edit all data
         * DELETE - can delete own data
         * DELETE-ALL - can delete all data
         */

        $this->deny('guest');

        $this->allow('guest', 'front');
        
        $this->allow('client', 'admin', 'view');
        $this->allow('client', 'dashboard', 'view');
        $this->allow('client', 'tasks', 'view');
        $this->allow('client', 'tasks', 'add');
        $this->allow('client', 'tasks', 'edit');
        $this->allow('client', 'comments', 'view');
        $this->allow('client', 'projects', 'view');        

        $this->allow('manager', 'companies');
        $this->allow('manager', 'tasks');
        $this->allow('manager', 'comments');
        $this->allow('manager', 'projects');

        $this->allow('admin', 'users');

        $this->allow('superadmin'); // všechna práva a zdroje pro administrátora
    }

}
