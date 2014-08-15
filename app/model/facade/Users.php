<?php

namespace App\Model\Facade;

use App\Model\Entity\User;


class Users extends Base
{
	
	public function __construct()
	{
		parent::__construct();
		
	}

    /**
     * 
     * @param type $email
     * @return User
     */
    public function findByEmail($email)
    {
        return $this->dao->findOneBy(['email' => $email]);
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
            $entity = new User;
            $entity->email = $email;
            $entity->password = $password;
            return $this->save($entity); // ToDo: Delete this line
        }
        return NULL;
    }

}
