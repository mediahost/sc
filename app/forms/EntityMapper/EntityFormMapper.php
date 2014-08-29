<?php

namespace App\Forms\EntityMapper;

use App\Model\Entity,
	Kdyby\Doctrine\EntityManager,
	App\Model\Facade,
	Kdyby\Doctrine\EntityDao,
	App\Model\Facade\UserFacade as UserFacade;

use Tracy\Debugger as Debug;

/**
 * EntityFormMapper
 *
 * @author Petr Poupě
 */
class EntityFormMapper extends \Kdyby\DoctrineForms\EntityFormMapper
{
	
	/** @var EntityManager */
	private $em;
	
	/** @var EntityDao */
	private $roleDao;
	
	/** @var EntityDao */
	private $authDao;
	
	/** @var UserFacade */
	private $userFacade;


	public function __construct(\Doctrine\ORM\EntityManager $entityManager, EntityManager $em, UserFacade $userFacade)
	{
		parent::__construct($entityManager);
		$this->em = $em;
		$this->roleDao = $this->em->getDao(Entity\Role::getClassName());
		$this->authDao = $this->em->getDao(Entity\Auth::getClassName());
		$this->userFacade = $userFacade;
	}

	public function load($entity, $form)
	{
		if ($entity instanceof Entity\User) {
			$form->setValues([
				'mail' => $entity->email,
				'roles' => $entity->getRolesKeys(),
			]);
		} else {
			parent::load($entity, $form);
		}
	}

	public function save($entity, $form)
	{
		if ($entity instanceof Entity\User) {
			if (isset($form->values->mail)) {
				$entity->email = $form->values->mail;
			}

			if ($form->values->password !== NULL && $form->values->password !== "") {
				$this->userFacade->setAppPassword($entity, $form->values->password);
			}
			
			$entity->clearRoles();
			
			foreach ($form->values->roles as $id) {
				$item = $this->roleDao->find($id);
				if ($item) {
					$entity->addRole($item);
				}
			}
		} else {
			parent::save($entity, $form);
		}
	}

}
