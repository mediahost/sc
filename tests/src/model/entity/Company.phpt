<?php

namespace Test\Model\Entity;

use App\Model\Entity\Company;
use App\Model\Entity\CompanyPermission;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\User;
use Tester\Assert;
use Tester\TestCase;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: Company entity
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyTest extends TestCase
{

	public function testSetAndGet()
	{
		$name = 'Testovací společnost';
		$comapnyId = '123-345-567 99';
		$address = 'Silniční 123, Město nad Řekou';
		$user1 = new User('user1@mail.com');
		$user2 = new User('user2@mail.com');
		$user3 = new User('user3@mail.com');
		$roleAdmin = new CompanyRole(CompanyRole::ADMIN);
		$roleManager = new CompanyRole(CompanyRole::MANAGER);
		$roleEditor = new CompanyRole(CompanyRole::EDITOR);

		$entity = new Company($name);
		$entity->companyId = $comapnyId;
		$entity->address = $address;

		Assert::null($entity->id);
		Assert::same($name, $entity->name);
		Assert::same($name, (string) $entity);
		Assert::same($comapnyId, $entity->companyId);
		Assert::same($address, $entity->address);

		Assert::exception(function() use ($entity) {
			$entity->id = 123;
		}, 'Kdyby\Doctrine\MemberAccessException');
		
		$permission1 = new CompanyPermission;
		$permission1->company = $entity;
		$permission1->user = $user1;
		$permission1->addRole($roleAdmin);
		$permission2 = new CompanyPermission;
		$permission2->company = $entity;
		$permission2->user = $user2;
		$permission2->addRole($roleAdmin);
		$permission2->addRole($roleManager);
		$permission3 = new CompanyPermission;
		$permission3->company = $entity;
		$permission3->user = $user3;
		$permission3->addRole($roleEditor);
		
		Assert::count(0, $entity->accesses);
		$entity->addAccess($permission1);
		$entity->addAccess($permission2);
		$entity->addAccess($permission3);
		Assert::count(3, $entity->accesses);
		
		Assert::count(2, $entity->adminAccesses);
		Assert::count(1, $entity->managerAccesses);
		Assert::count(1, $entity->editorAccesses);
		
		$entity->clearAccesses();
		Assert::count(0, $entity->accesses);
	}

}

$test = new CompanyTest();
$test->run();
