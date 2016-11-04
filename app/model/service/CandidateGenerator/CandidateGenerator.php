<?php

namespace App\Model\Service;

use App\Model\Entity\Address;
use App\Model\Entity\JobCategory;
use App\Model\Entity\Person;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\ValuesGenerator;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

class CandidateGenerator extends Object
{

	const USER_MAIL_SUFFIX = '@example.dev';
	const USER_PASSWORD = 'user';
	const TMP_CV = 'files/example.pdf';

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var EntityManager @inject */
	public $em;

	public function createCandidate()
	{
		$user = $this->createUser();
		$user->verificated = 1;
		$person = $this->completePerson($user);
		$candidate = $this->completeCandidate($person);
		$this->em->getRepository(User::getClassName())->save($user);
		return $candidate;
	}

	private function createUser()
	{
		$roleCandidate = $this->roleFacade->findByName(Role::CANDIDATE);
		$name = ValuesGenerator::generateName(100);
		$user = $this->userFacade->create($name . self::USER_MAIL_SUFFIX, self::USER_PASSWORD, $roleCandidate);
		return $user;
	}

	private function completePerson(User $user)
	{
		$person = $user->getPerson();

		$person->title = ValuesGenerator::selectIndexFromList(Person::getTitleList());
		$person->degreeBefore = ValuesGenerator::generateName(60);
		$person->middlename = ValuesGenerator::generateName(10);
		if (ValuesGenerator::isFilled(90)) {
			$person->firstname = ValuesGenerator::generateName(100);
			$person->surname = ValuesGenerator::generateName(100);
		}
		$person->degreeAfter = ValuesGenerator::generateName(20);
		$person->gender = ValuesGenerator::selectIndexFromList(Person::getGenderList());
		$person->birthday = ValuesGenerator::generatePastDate();
		$person->nationality = ValuesGenerator::selectIndexFromList(Person::getNationalityList());
		$person->phone = ValuesGenerator::generateNumberString(9);

		$address = new Address();
		$address->house = ValuesGenerator::generateNumberString(3);
		$address->street = ValuesGenerator::generateName();
		$address->zipcode = ValuesGenerator::generateNumberString(5);
		$address->city = ValuesGenerator::generateName();
		$address->country = ValuesGenerator::selectIndexFromList(Person::getLocalities());
		$person->address = $address;

		return $person;
	}

	private function completeCandidate(Person $person)
	{
		$candidate = $person->getCandidate();
		$candidate->cvFile = 'default';
		$candidate->freelancer = ValuesGenerator::isFilled();

		$localities = [];
		foreach (Person::getLocalities() as $group) {
			$localities = array_merge($localities, $group);
		}
		$candidate->workLocations = ValuesGenerator::selectMultiIndexFromList($localities);

		$jobCategories = $this->em->getRepository(JobCategory::getClassName())->findAll();
		$selectedCategories = ValuesGenerator::selectMultiValuesFromList($jobCategories);
		foreach ($selectedCategories as $category) {
			$candidate->addJobCategory($category);
		}
		return $candidate;
	}
}