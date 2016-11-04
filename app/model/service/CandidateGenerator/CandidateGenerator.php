<?php

namespace App\Model\Service;

use App\Model\Entity\Address;
use App\Model\Entity\Candidate;
use App\Model\Entity\Competences;
use App\Model\Entity\Cv;
use App\Model\Entity\Education;
use App\Model\Entity\Language;
use App\Model\Entity\Person;
use App\Model\Entity\Referee;
use App\Model\Entity\Role;
use App\Model\Entity\Sender;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillKnow;
use App\Model\Entity\SkillLevel;
use App\Model\Entity\User;
use App\Model\Entity\Work;
use App\Model\Facade\JobFacade;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;
use Nette\Utils\DateTime;

class CandidateGenerator extends Object
{
	/** @var UserFacade @inject */
	public $userFacade;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var JobFacade @inject */
	public $jobFacade;

	/** @var EntityManager @inject */
	public $em;


	public function removeGeneratedCandidates()
	{
		$useRepo = $this->em->getRepository(User::getClassName());
		$users = $useRepo->createQueryBuilder('u')
			->where("u.mail LIKE '%example.dev'")
			->getQuery()->getResult();

		$senders = $this->em->getRepository(Sender::getClassName())->createQueryBuilder('s')
			->leftJoin('s.user', "u")
			->where('u IN(:user)')
			->setParameter('user', $users)
			->getQuery()->getResult();

		foreach ($senders as $sender) {
			$this->em->remove($sender);
		}
		foreach ($users as $user) {
			$this->em->remove($user);
		}
		$this->em->flush();
	}

	public function createCandidate()
	{
		$user = $this->createUser();
		$user->verificated = 1;
		$person = $this->completePerson($user);
		$candidate = $this->completeCandidate($person);
		$cv = $this->createCv($candidate);
		$this->fillWorksAndExperiences($cv);
		$this->fillSkills($cv);
		$this->fillLanguages($cv);

		$this->em->getRepository(User::getClassName())->save($user);
	}

	private function createUser()
	{
		$roleCandidate = $this->roleFacade->findByName(Role::CANDIDATE);
		$name = $this->generateName(100);
		$user = $this->userFacade->create("{$name}@example.dev", "randomuser", $roleCandidate);
		return $user;
	}

	private function completePerson(User $user)
	{
		$person = $user->getPerson();

		$person->title = $this->selectIndexFromList(Person::getTitleList());
		$person->degreeBefore = $this->generateName(60);
		$person->firstname = $this->generateName();
		$person->middlename = $this->generateName(10);
		$person->surname = $this->generateName();
		$person->degreeAfter = $this->generateName(20);
		$person->gender = $this->selectIndexFromList(Person::getGenderList());
		$person->birthday = $this->generatePastDate();
		$person->nationality = $this->selectIndexFromList(Person::getNationalityList());
		$person->phone = $this->generateNumberString(9);

		$address = new Address();
		$address->house = $this->generateNumberString(3);
		$address->street = $this->generateName();
		$address->zipcode = $this->generateNumberString(5);
		$address->city = $this->generateName();
		$address->country = $this->selectIndexFromList(Person::getLocalities());
		$person->address = $address;

		return $person;
	}

	private function completeCandidate(Person $person)
	{
		$candidate = $person->getCandidate();
		$candidate->cvFile = 'default';
		$candidate->freelancer = $this->isFilled();

		$localities = [];
		foreach (Person::getLocalities() as $group) {
			$localities = array_merge($localities, $group);
		}
		$candidate->workLocations = $this->selectMultiFromList($localities);

		$jobCategories = $this->jobFacade->findCategoriesPairs();
		$candidate->jobCategories = $this->selectMultiFromList($jobCategories);
		return $candidate;
	}

	private function createCv(Candidate $candidate)
	{
		$cv = $candidate->getCv();

		$education = new Education();
		$education->institution = $this->generateName(100);
		$education->title = $this->generateName();
		$education->dateStart = $this->generatePastDate();
		$interval = rand(30, 1000);
		$education->dateEnd = $education->dateStart->modifyClone("+{$interval} days");
		$education->subjects = $this->generateName();
		$education->address = new Address();
		$education->address->city = $this->generateName();
		$education->address->country = $this->selectIndexFromList(Person::getLocalities());
		$cv->addEducation($education);

		$competence = new Competences();
		$competence->social = $this->generateName();
		$competence->organisation = $this->generateName();
		$competence->technical = $this->generateName();
		$competence->artictic = $this->generateName();
		$competence->other = $this->generateName();
		$competence->drivingLicenses = $this->selectMultiFromList(Competences::getDrivingLicensesList());
		$cv->competence = $competence;

		$cv->careerObjective = $this->generateText();
		$cv->careerSummary = $this->generateText();
		$cv->additionalInfo = $this->generateText();

		$jobCategories = $this->jobFacade->findCategoriesPairs();
		$cv->desiredPosition = $this->selectValueFromList($jobCategories);
		$cv->availableFrom = $this->generateFeatureDate();
		$cv->salaryFrom = $this->generateNumberString(4);
		$interval = rand(5, 50) * 0.01 * $cv->salaryFrom;
		$cv->salaryTo = $cv->salaryFrom + $interval;

		return $cv;
	}

	private function fillWorksAndExperiences(Cv $cv)
	{
		$count = rand(0, 3);
		while ($count--) {
			$work = new Work();
			$work->isExperience = $this->isFilled();

			$work->company = $this->generateName(100);
			$work->position = $this->generateName();
			$work->dateStart = $this->generatePastDate();
			$interval = rand(30, 1000);
			$work->dateEnd = $work->dateStart->modifyClone("+{$interval} days");
			$work->activities = $this->generateName();
			$work->achievment = $this->generateName();
			$work->refereeIsPublic = $this->isFilled();

			$work->referee = new Referee();
			$work->referee->name = $this->generateName();
			$work->referee->position = $this->generateName();
			$work->referee->phone = $this->generateNumberString(9, 100);
			$work->referee->mail = "{$this->generateName(100)}@example.dev";
			$cv->addWork($work);
		}
	}

	private function fillSkills(Cv $cv)
	{
		$skills = $this->em->getRepository(Skill::getClassName())->findAll();
		$skillLevels = $this->em->getRepository(SkillLevel::getClassName())->findAll();
		$selectedSkills = $this->selectMultiFromList($skills);
		foreach ($selectedSkills as $idSkill) {
			$skillKnow = new SkillKnow();
			$skillKnow->skill = $skills[$idSkill];
			$skillKnow->level = $skillLevels[rand(0, 4)];
			$skillKnow->years = rand(1, 10);
			$skillKnow->cv = $cv;
			$cv->skillKnow = $skillKnow;
		}
	}

	private function fillLanguages(Cv $cv)
	{
		$cv->motherLanguage = $this->selectIndexFromList(Language::getLanguagesList());
		$languages = $this->selectMultiFromList(Language::getLanguagesList(), rand(0, 5));
		foreach ($languages as $selectedLanguage) {
			$language = new Language();
			$language->language = $selectedLanguage;
			$language->listening = rand(0, 4);
			$language->reading = rand(0, 4);
			$language->spokenInteraction = rand(0, 4);
			$language->spokenProduction = rand(0, 4);
			$language->writing = rand(0, 4);
			$cv->addLanguage($language);
		}
	}

	private function isFilled($fillProbability=50)
	{
		return rand(0, 99) < $fillProbability;
	}

	private function selectIndexFromList($list, $fillProbability=50)
	{
		if (!$this->isFilled($fillProbability)) {
			return null;
		}
		$keys = array_keys($list);
		return $keys[rand(0, count($keys)-1)];
	}

	private function selectValueFromList($list, $fillProbability=50)
	{
		if (!$this->isFilled($fillProbability)) {
			return null;
		}
		$keys = array_keys($list);
		$key = $keys[rand(0, count($keys)-1)];
		return $list[$key];
	}

	private function selectMultiFromList($list, $valuesCount=null)
	{
		$result = [];
		$keys = array_keys($list);
		$valuesCount = $valuesCount  ?  $valuesCount : rand(0, count($keys)-1);
		while ($valuesCount--) {
			$result[] = $keys[rand(0, count($keys)-1)];
		}
		return $result;
	}

	private function generatePastDate()
	{
		$daysToPast = rand(20*365, 60*365);
		$date = new DateTime();
		return $date->modify("-{$daysToPast}days");
	}

	private function generateFeatureDate()
	{
		$daysInFeature = rand(10, 100);
		$date = new DateTime();
		return $date->modify("+{$daysInFeature}days");
	}

	private function generateNumberString($length, $fillProbability=50)
	{
		if (!$this->isFilled($fillProbability)) {
			return null;
		}
		$digits = '1234567890';
		$result = '';
		while ($length--) {
			$result .= $digits[rand(0, strlen($digits)-1)];
		}
		return $result;
	}

	private function generateName($fillProbability=50)
	{
		if (!$this->isFilled($fillProbability)) {
			return '';
		}
		$result = '';
		$vowels = 'aeiouy';
		$consonants = 'bcdfghjklmnprstvwz';

		$syllableCount = rand(2, 4);
		while ($syllableCount--) {
			$syllable = $consonants[rand(0, strlen($consonants)-1)]
				. $vowels[rand(0, strlen($vowels)-1)];
			$result .= $syllable;
		}
		return $result;
	}

	private function generateText($fillProbability=50)
	{
		if (!$this->isFilled($fillProbability)) {
			return '';
		}
		$result = '';
		$wordsCount = rand(1, 50);
		while ($wordsCount--) {
			$result .= $this->generateName(100);
		}
		return $result;
	}
}