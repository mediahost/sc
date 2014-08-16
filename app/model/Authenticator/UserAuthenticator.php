<?php

namespace App\Model\Authenticator;

use Nette;

/**
 * Users Authenticator.
 */
class UserAuthenticator extends Nette\Object implements Nette\Security\IAuthenticator
{

    /** @var \App\Model\Facade\User */
    private $userFacade;

	/** @var \App\Model\Facade\Auth */
    private $auths;
	
	
    public function __construct(\App\Model\Facade\User $userFacade, \App\Model\Facade\Auth $auths)
    {
        $this->userFacade = $userFacade;
		$this->auths = $auths;
    }

    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($email, $password) = $credentials;

        $auth = $this->auths->findByEmail($email);

        if (!$auth) {
            throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
        } elseif (!\Nette\Security\Passwords::verify($password, $auth->hash)) { // ToDo: Tohle by mělo být v uivateli, v auth nebo v Nette
            throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
        } elseif (\Nette\Security\Passwords::needsRehash($auth->hash)) {
            $auth->hash = \Nette\Security\Passwords::hash($password);
            $this->auths->save($auth);
        }
		
		$user = $auth->user;
        
        $arr = $user->toArray();
        return new Nette\Security\Identity($user->getId(), $user->getRolesPairs(), $arr);
    }

}





//
//namespace App\Model;
//
//use Nette,
//	Nette\Utils\Strings,
//	Nette\Security\Passwords;
//
//
///**
// * Users management.
// */
//class UserManager extends Nette\Object implements Nette\Security\IAuthenticator
//{
//	const
//		TABLE_NAME = 'users',
//		COLUMN_ID = 'id',
//		COLUMN_NAME = 'username',
//		COLUMN_PASSWORD_HASH = 'password',
//		COLUMN_ROLE = 'role';
//
//
//	/** @var Nette\Database\Context */
//	private $database;
//
//
//	public function __construct(Nette\Database\Context $database)
//	{
//		$this->database = $database;
//	}
//
//
//	/**
//	 * Performs an authentication.
//	 * @return Nette\Security\Identity
//	 * @throws Nette\Security\AuthenticationException
//	 */
//	public function authenticate(array $credentials)
//	{
//		list($username, $password) = $credentials;
//
//		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $username)->fetch();
//
//		if (!$row) {
//			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
//
//		} elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
//			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
//
//		} elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
//			$row->update(array(
//				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
//			));
//		}
//
//		$arr = $row->toArray();
//		unset($arr[self::COLUMN_PASSWORD_HASH]);
//		return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
//	}
//
//
//	/**
//	 * Adds new user.
//	 * @param  string
//	 * @param  string
//	 * @return void
//	 */
//	public function add($username, $password)
//	{
//		$this->database->table(self::TABLE_NAME)->insert(array(
//			self::COLUMN_NAME => $username,
//			self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
//		));
//	}
//
//}
//
