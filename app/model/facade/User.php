<?php

namespace App\Model\Facade;

use App\Model\Entity\User,
	Kdyby\Doctrine\EntityDao;


class User extends Base
{
	
	/** @var EntityDao */
	private $users;
	
	protected function init()
	{
		$this->users = $this->em->getDao(User::getClassName());
	}

	/**
     * 
     * @param type $email
     * @return User
     */
    public function findByEmail($email)
    {
        return $this->users->findOneBy(['email' => $email]);
    }
    
    /**
     * Check if email is unique
     * @param type $email
     * @return bool
     */
    public function isUnique($email)
    {
        return $this->findByEmail($email) === NULL;
    }
    
    /**
     * Create user if isnt exists
     * @param type $email
     * @param type $password
     * @return \App\Model\Entity\User|null
     */
    public function create($email, $password)
    {
        if ($this->findByEmail($email) === NULL) { // check unique
            $user = new User;
            $user->email = $email;
            $user->password = $password;
            return $this->users->save($user); // ToDo: Delete this line
        }
        return NULL;
    }

}
