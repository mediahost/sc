<?php

namespace App\Model\Service;

use App\Model\Entity\Address;
use App\Model\Entity\Candidate;
use App\Model\Entity\Competences;
use App\Model\Entity\Cv;
use App\Model\Entity\Education;
use App\Model\Entity\JobCategory;
use App\Model\Entity\Language;
use App\Model\Entity\Person;
use App\Model\Entity\Referee;
use App\Model\Entity\Role;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillKnow;
use App\Model\Entity\SkillLevel;
use App\Model\Entity\User;
use App\Model\Entity\Work;
use App\Model\Facade\JobFacade;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\ValuesGenerator;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

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
		$name = ValuesGenerator::generateName(100);
		$user = $this->userFacade->create("{$name}@example.dev", "randomuser", $roleCandidate);
		return $user;
	}

	private function completePerson(User $user)
	{
		$person = $user->getPerson();

		$person->title = ValuesGenerator::selectIndexFromList(Person::getTitleList());
		$person->degreeBefore = ValuesGenerator::generateName(60);
		$person->firstname = ValuesGenerator::generateName();
		$person->middlename =  ValuesGenerator::generateName(10);
		$person->surname =  ValuesGenerator::generateName();
		$person->degreeAfter =  ValuesGenerator::generateName(20);
		$person->gender =  ValuesGenerator::selectIndexFromList(Person::getGenderList());
		$person->birthday =  ValuesGenerator::generatePastDate();
		$person->nationality =  ValuesGenerator::selectIndexFromList(Person::getNationalityList());
		$person->phone =  ValuesGenerator::generateNumberString(9);

		$address = new Address();
		$address->house =  ValuesGenerator::generateNumberString(3);
		$address->street =  ValuesGenerator::generateName();
		$address->zipcode =  ValuesGenerator::generateNumberString(5);
		$address->city =  ValuesGenerator::generateName();
		$address->country =  ValuesGenerator::selectIndexFromList(Person::getLocalities());
		$person->address = $address;

		return $person;
	}

	private function completeCandidate(Person $person)
	{
		$candidate = $person->getCandidate();
		$candidate->cvFile = 'default';
		$candidate->freelancer =  ValuesGenerator::isFilled();

		$localities = [];
		foreach (Person::getLocalities() as $group) {
			$localities = array_merge($localities, $group);
		}
		$candidate->workLocations =  ValuesGenerator::selectMultiIndexFromList($localities);

		$jobCategories = $this->em->getRepository(JobCategory::getClassName())->findAll();
		$selectedCategories = ValuesGenerator::selectMultiValuesFromList($jobCategories);
		foreach ($selectedCategories as $category) {
			$candidate->addJobCategory($category);
		}
		return $candidate;
	}

	private function createCv(Candidate $candidate)
	{
		$cv = $candidate->getCv();

		$education = new Education();
		$education->institution =  ValuesGenerator::generateName(100);
		$education->title =  ValuesGenerator::generateName();
		$education->dateStart =  ValuesGenerator::generatePastDate();
		$interval = rand(30, 1000);
		$education->dateEnd = $education->dateStart->modifyClone("+{$interval} days");
		$education->subjects =  ValuesGenerator::generateName();
		$education->address = new Address();
		$education->address->city =  ValuesGenerator::generateName();
		$education->address->country =  ValuesGenerator::selectIndexFromList(Person::getLocalities());
		$cv->addEducation($education);

		$competence = new Competences();
		$competence->social =  ValuesGenerator::generateName();
		$competence->organisation =  ValuesGenerator::generateName();
		$competence->technical =  ValuesGenerator::generateName();
		$competence->artictic =  ValuesGenerator::generateName();
		$competence->other =  ValuesGenerator::generateName();
		$competence->drivingLicenses =  ValuesGenerator::selectMultiIndexFromList(Competences::getDrivingLicensesList());
		$cv->competence = $competence;

		$cv->careerObjective =  ValuesGenerator::generateText();
		$cv->careerSummary =  ValuesGenerator::generateText();
		$cv->additionalInfo =  ValuesGenerator::generateText();

		$jobCategories = $this->jobFacade->findCategoriesPairs();
		$cv->desiredPosition =  ValuesGenerator::selectValueFromList($jobCategories);
		$cv->availableFrom =  ValuesGenerator::generateFeatureDate();
		$cv->salaryFrom =  ValuesGenerator::generateNumberString(4);
		$interval = rand(5, 50) * 0.01 * $cv->salaryFrom;
		$cv->salaryTo = $cv->salaryFrom + $interval;

		return $cv;
	}

	private function fillWorksAndExperiences(Cv $cv)
	{
		$count = rand(0, 3);
		while ($count--) {
			$work = new Work();
			$work->isExperience =  ValuesGenerator::isFilled();

			$work->company =  ValuesGenerator::generateName(100);
			$work->position =  ValuesGenerator::generateName();
			$work->dateStart =  ValuesGenerator::generatePastDate();
			$interval = rand(30, 1000);
			$work->dateEnd =  $work->dateStart->modifyClone("+{$interval} days");
			$work->activities =  ValuesGenerator::generateName();
			$work->achievment =  ValuesGenerator::generateName();
			$work->refereeIsPublic =  ValuesGenerator::isFilled();

			$work->referee = new Referee();
			$work->referee->name =  ValuesGenerator::generateName();
			$work->referee->position =  ValuesGenerator::generateName();
			$work->referee->phone =  ValuesGenerator::generateNumberString(9, 100);
			$mail = ValuesGenerator::generateName(100);
			$work->referee->mail = "{$mail}@example.dev";
			$cv->addWork($work);
		}
	}

	private function fillSkills(Cv $cv)
	{
		$skills = $this->em->getRepository(Skill::getClassName())->findAll();
		$skillLevels = $this->em->getRepository(SkillLevel::getClassName())->findAll();
		$selectedSkills = ValuesGenerator::selectMultiIndexFromList($skills);
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
		$cv->motherLanguage = ValuesGenerator::selectIndexFromList(Language::getLanguagesList());
		$languages = ValuesGenerator::selectMultiIndexFromList(Language::getLanguagesList(), rand(0, 5));
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
}