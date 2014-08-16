<?php

namespace App\Forms\EntityMapper;

use App\Model\Entity,
    Doctrine\ORM\EntityManager,
    App\Model\Facade;
use Tracy\Debugger as Debug;

/**
 * EntityFormMapper
 *
 * @author Petr Poupě
 */
class EntityFormMapper extends \Kdyby\DoctrineForms\EntityFormMapper
{

    /** @var Facade\Roles */
    private $roles;

    /** @var Facade\Users */
    private $users;

    public function __construct(EntityManager $entityManager, Facade\Roles $roles, Facade\Users $users)
    {
        parent::__construct($entityManager);
        $this->roles = $roles;
        $this->users = $users;
    }

    public function load($entity, $form)
    {
        if ($entity instanceof Entity\User) {
            $form->setValues(array(
                "username" => $entity->getUsername(),
                "roles" => $entity->getRolesArray(TRUE),
            ));
        } else if ($entity instanceof Entity\Company) {
            $form->setValues(array(
                "name" => $entity->getName(),
                "users" => $entity->getUsersArray(TRUE),
            ));
        } else if ($entity instanceof Entity\Project) {
            $form->setValues(array(
                "name" => $entity->getName(),
                "company" => $entity->getCompany() === NULL ? NULL : $entity->getCompany()->getId(),
            ));
        } else{
            parent::load($entity, $form);
        }
    }

    public function save($entity, $form)
    {
        if ($entity instanceof Entity\User) {
            $entity->setUsername($form->values->username);
            if ($form->values->password !== NULL && $form->values->password !== "") {
                $entity->setPassword($form->values->password);
            }
            $entity->clearRoles();
            foreach ($form->values->roles as $id) {
                $item = $this->roleFacade->find($id);
                if ($item) {
                    $entity->addRole($item);
                }
            }
        } else if ($entity instanceof Entity\Company) {
            $entity->setName($form->values->name);
            $entity->clearUsers();
            foreach ($form->values->users as $id) {
                $item = $this->userFacade->find($id);
                if ($item) {
                    $entity->addUser($item);
                }
            }
        } else if ($entity instanceof Entity\Company) {
            $entity->setName($form->values->name);
            $entity->setCompany($this->companyFacade->find($id));
        } else if ($entity instanceof Entity\Task) {
            if ($form->values->solver === NULL) {
                $entity->resetSolver();
            }
            parent::save($entity, $form);
        } else if ($entity instanceof Entity\Comment) {
            parent::save($entity, $form);
            if ($form->values->message !== NULL) {
                $message = htmlspecialchars($form->values->message);
                $entity->setMessage(\App\Helpers::linkToAnchor($message));
            }
            if ($form->values->minutes > 0) {
                $entity->setMinutes($form->values->minutes);
            }
        } else {
            parent::save($entity, $form);
        }
    }

}
