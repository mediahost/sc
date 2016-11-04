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
use App\Model\Entity\Skill;
use App\Model\Entity\SkillKnow;
use App\Model\Entity\SkillLevel;
use App\Model\Entity\Work;
use App\Model\Facade\JobFacade;
use App\ValuesGenerator;
use Doctrine\ORM\EntityManager;
use Nette\Object;

class CvGenerator extends Object
{

	/** @var EntityManager @inject */
	public $em;

	/** @var JobFacade @inject */
	public $jobFacade;

	public function createCv(Candidate $candidate)
	{
		$cv = $candidate->getCv();
		$this->fillWorksAndExperiences($cv);
		$this->fillEducations($cv);
		$this->fillCompetences($cv);
		$this->fillSkills($cv);
		$this->fillLanguages($cv);

		$cv->careerObjective = ValuesGenerator::generateText();
		$cv->careerSummary = ValuesGenerator::generateText();
		$cv->additionalInfo = ValuesGenerator::generateText();

		$jobCategories = $this->jobFacade->findCategoriesPairs();
		$cv->desiredPosition = ValuesGenerator::selectValueFromList($jobCategories);
		$cv->availableFrom = ValuesGenerator::generateFeatureDate();
		$cv->salaryFrom = ValuesGenerator::generateNumberString(4);
		$interval = rand(5, 50) * 0.01 * $cv->salaryFrom;
		$cv->salaryTo = $cv->salaryFrom + $interval;

		$this->em->getRepository(Cv::getClassName())->save($cv);
		return $cv;
	}

	private function fillWorksAndExperiences(Cv $cv)
	{
		$count = rand(0, 3);
		while ($count--) {
			$work = new Work();
			$work->isExperience = ValuesGenerator::isFilled();

			$work->company = ValuesGenerator::generateName(100);
			$work->position = ValuesGenerator::generateName();
			$work->dateStart = ValuesGenerator::generatePastDate();
			$interval = rand(30, 1000);
			$work->dateEnd = $work->dateStart->modifyClone("+{$interval} days");
			$work->activities = ValuesGenerator::generateName();
			$work->achievment = ValuesGenerator::generateName();
			$work->refereeIsPublic = ValuesGenerator::isFilled();

			$work->referee = new Referee();
			$work->referee->name = ValuesGenerator::generateName(100);
			$work->referee->position = ValuesGenerator::generateName(100);
			$work->referee->phone = ValuesGenerator::generateNumberString(9, 100);
			$mail = ValuesGenerator::generateName(100);
			$work->referee->mail = "{$mail}@example.dev";
			$cv->addWork($work);
		}
	}

	private function fillEducations(Cv $cv)
	{
		$education = new Education();
		$education->institution = ValuesGenerator::generateName(100);
		$education->title = ValuesGenerator::generateName();
		$education->dateStart = ValuesGenerator::generatePastDate();
		$interval = rand(30, 1000);
		$education->dateEnd = $education->dateStart->modifyClone("+{$interval} days");
		$education->subjects = ValuesGenerator::generateName();
		$education->address = new Address();
		$education->address->city = ValuesGenerator::generateName();
		$education->address->country = ValuesGenerator::selectIndexFromList(Person::getLocalities());
		$cv->addEducation($education);
	}

	private function fillCompetences(Cv $cv)
	{
		$competence = new Competences();
		$competence->social = ValuesGenerator::generateName();
		$competence->organisation = ValuesGenerator::generateName();
		$competence->technical = ValuesGenerator::generateName();
		$competence->artictic = ValuesGenerator::generateName();
		$competence->other = ValuesGenerator::generateName();
		$competence->drivingLicenses = ValuesGenerator::selectMultiIndexFromList(Competences::getDrivingLicensesList());
		$cv->competence = $competence;
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