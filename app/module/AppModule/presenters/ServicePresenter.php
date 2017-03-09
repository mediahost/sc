<?php

namespace App\AppModule\Presenters;

use App\Components\User\Form\CsvUserImport;
use App\Components\User\Form\ICsvUserImportFactory;
use App\Extensions\Csv\Exceptions\InternalException;
use App\Extensions\Installer;
use App\Model\Entity\Address;
use App\Model\Entity\Candidate;
use App\Model\Entity\Company;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\Cv;
use App\Model\Entity\ImportedUser;
use App\Model\Entity\Job;
use App\Model\Entity\Role;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use App\Model\Entity\SkillKnow;
use App\Model\Entity\SkillLevel;
use App\Model\Entity\User;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use App\Model\Service\CandidateCleaner;
use App\Model\Service\CandidateGenerator;
use App\Model\Service\CvGenerator;
use Doctrine\ORM\Tools\SchemaTool;
use Kdyby\Doctrine\Connection;
use Kdyby\Doctrine\Helpers;
use Nette\Utils\FileSystem;
use Nette\Utils\Random;
use Nette\Utils\Strings;

class ServicePresenter extends BasePresenter
{

	/** @var Connection @inject */
	public $connection;

	/** @var Installer @inject */
	public $installer;

	/** @var RoleFacade @inject */
	public $roleFacade;

	/** @var UserFacade @inject */
	public $userFacade;

	/** @var CompanyFacade @inject */
	public $companyFacade;

	/** @var CandidateGenerator @inject */
	public $candidateGenerator;

	/** @var CvGenerator @inject */
	public $cvGenerator;

	/** @var CandidateCleaner @inject */
	public $candidateCleaner;

	/** @var ICsvUserImportFactory @inject */
	public $iCsvUserImportFactory;

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('default')
	 */
	public function actionDefault()
	{
		$this->redirect('tools');
	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('imports')
	 */
	public function actionImports()
	{

	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('tools')
	 */
	public function actionTools()
	{

	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('creators')
	 */
	public function actionCreators()
	{

	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('testData')
	 */
	public function actionTestData()
	{

	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('importInitData')
	 */
	public function handleImportInitData()
	{
		$this->reinstall();
		$this->importDbAll();
		$message = $this->translator->translate('Data was imported from SQL files');
		$this->flashMessage($message, 'success');
		$this->redirect('this');
	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('install')
	 */
	public function handleInstall()
	{
		$this->install();
		$message = $this->translator->translate('DB was instaled');
		$this->flashMessage($message, 'success');
		$this->redirect('this');
	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('reinstall')
	 */
	public function handleReinstall()
	{
		$this->reinstall();
		$message = $this->translator->translate('DB was reinstaled');
		$this->flashMessage($message, 'success');
		$this->redirect('this');
	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('createSkills')
	 */
	public function handleCreateSkills($categoriesCnt, $subcategoriesCnt, $skillsCnt)
	{
		$this->createSkills($categoriesCnt, $subcategoriesCnt, $skillsCnt);
		$message = $this->translator->translate('Skills and cateories was succesfully created');
		$this->flashMessage($message, 'success');
		$this->redirect('this');
	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('createCompanies')
	 */
	public function handleCreateCompanies($companiesCnt, $jobsCnt)
	{
		$this->createCompanies($companiesCnt, $jobsCnt);
		$message = $this->translator->translate('Candidates and their CVs was succesfully created');
		$this->flashMessage($message, 'success');
		$this->redirect('this');
	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('createJobCategories')
	 */
	public function handleCreateJobCategories()
	{
		$this->importDbJobCategories();
		$message = $this->translator->translate('Job categories was succesfully created');
		$this->flashMessage($message, 'success');
		$this->redirect('this');
	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('generateCandidates')
	 */
	public function handleGenerateCandidates()
	{
		for ($i = 0; $i < CandidateGenerator::COUNT_GENERATED; $i++) {
			$candidate = $this->candidateGenerator->createCandidate();
			if ($candidate) {
				$this->cvGenerator->createCv($candidate);
			}
		}
		$message = $this->translator->translate('Candidates and their CVs was succesfully created');
		$this->flashMessage($message, 'success');
		$this->redirect('this');
	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('clearTestData')
	 */
	public function handleClearTestData()
	{
		$this->candidateCleaner->removeGeneratedCandidates();
		$this->redirect('this');
	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('updateCandidates')
	 */
	public function handleUpdateCandidates()
	{
		$this->updateCandidates();
		$this->redirect('this');
	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('importUsers')
	 */
	public function handleImportUsers()
	{
		$count = $this->importExistedUsers();
		$this->flashMessage($this->translator->translate('%count% users was edited.', $count), 'success');
		$this->redirect('this');
	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('importNewUsers')
	 */
	public function handleImportNewUsers()
	{
		$count = $this->importNewUsers();
		$this->flashMessage($this->translator->translate('%count% users was inserted.', $count), 'success');
		$this->redirect('this');
	}

	private function reinstall()
	{
		$this->uninstall();
		$this->install();
		return $this;
	}

	private function uninstall()
	{
		$schemaTool = new SchemaTool($this->em);
		$schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
		$this->em->clear();
		return $this;
	}

	private function install()
	{
		FileSystem::delete(realpath('./../temp/install/'));
		$this->installer
			->setInstallAdminer(FALSE)
			->setInstallComposer(FALSE);
		$this->installer->install();
		return $this;
	}

	private function importDbAll()
	{
		$this->importDbSkills();
		$this->importDbCandidates();
		$this->importDbCompanies();
		$this->importDbJobCategories();
		return $this;
	}

	private function importDbSkills()
	{
		$this->importDbDataFromFile(realpath('./../sql/skills.sql'));
		return $this;
	}

	/**
	 * Import all data for testing candidates (users, cvs)
	 * Required import skills (for cvs)
	 * @return self
	 */
	private function importDbCandidates()
	{
		$this->importDbDataFromFile(realpath('./../sql/candidates.sql'));
		return $this;
	}

	/**
	 * Import all data for testing companies (users, companies, jobs)
	 * Required import skills (for jobs)
	 * @return self
	 */
	private function importDbCompanies()
	{
		$this->importDbDataFromFile(realpath('./../sql/companies.sql'));
		return $this;
	}

	private function importDbJobCategories()
	{
		$this->importDbDataFromFile(realpath('./../sql/jobCategories.sql'));
		return $this;
	}

	private function importDbDataFromFile($file)
	{
		Helpers::loadFromFile($this->connection, $file);
	}

	/**
	 * Create Main categories, for each subcategories and for each subcategories their skills with generated names
	 * @param $categoriesCnt
	 * @param $subcategoriesCnt
	 * @param $skillsCnt
	 * @return self
	 */
	private function createSkills($categoriesCnt, $subcategoriesCnt, $skillsCnt)
	{
		for ($i = 1; $i <= $categoriesCnt; $i++) {
			$category = new SkillCategory("Main Category {$i}");
			$this->em->persist($category);
			for ($j = 1; $j <= $subcategoriesCnt; $j++) {
				$subCategory = new SkillCategory("SubCategory {$j} for {$i}");
				$subCategory->parent = $category;
				$this->em->persist($subCategory);
				for ($k = 1; $k <= $skillsCnt; $k++) {
					$skill = new Skill("Skill {$k} in Subcategory {$j} in main {$i}");
					$skill->category = $subCategory;
					$this->em->persist($skill);
				}
			}
		}
		$this->em->flush();
		return $this;
	}

	private function createCompanies($companiesCnt, $jobsCnt)
	{
		$roleCompany = $this->roleFacade->findByName(Role::COMPANY);
		// TODO: Start value to max ID
		$start = 0;
		for ($i = $start + 1; $i <= $start + $companiesCnt; $i++) {
			// create company with their users (admin, manager and editor)
			$admin = $this->userFacade->create("admin@company{$i}.com", 'admin', $roleCompany);
			$manager = $this->userFacade->create("manager@company{$i}.com", 'manager', $roleCompany);
			$editor = $this->userFacade->create("editor@company{$i}.com", 'editor', $roleCompany);
			$company = new Company('Company ' . $i);
			$company->companyId = Strings::webalize($company->name);
			$this->companyFacade->create($company, $admin);
			$this->companyFacade->addPermission($company, $manager, [CompanyRole::MANAGER]);
			$this->companyFacade->addPermission($company, $editor, [CompanyRole::EDITOR]);
			// create jobs
			for ($j = 1; $j <= $jobsCnt; $j++) {
				$job = $this->createJob($company, $j);
			}
		}
		return $this;
	}

	private function createJob(Company $company, $i)
	{
		$job = new Job("Job {$i} for {$company->companyId}");
		// TODO: finish
		return $job;
	}

	private function updateCandidates()
	{
		$candidateRepo = $this->em->getRepository(Candidate::getClassName());
		$candidates = $candidateRepo->findBy(['profileId' => NULL], [], 500);
		foreach ($candidates as $candidate) {
			$candidate->profileId = Random::generate(20);
			$this->em->persist($candidate);
		}
		$this->em->flush();
	}

	private function importExistedUsers()
	{
		$importedUserRepo = $this->em->getRepository(ImportedUser::getClassName());
		$userRepo = $this->em->getRepository(User::getClassName());

		$count = 0;
		$importedUsers = $importedUserRepo->findAll();
		foreach ($importedUsers as $importedUser) {
			$user = $userRepo->findOneByMail($importedUser->mail);
			if ($user) {
				$change = $this->loadUserData($user, $importedUser, FALSE);
				if ($change) {
					$userRepo->save($user);
					$count++;
				}
			}
		}
		return $count;
	}

	private function importNewUsers()
	{
		$importedUserRepo = $this->em->getRepository(ImportedUser::getClassName());
		$userRepo = $this->em->getRepository(User::getClassName());
		$roleRepo = $this->em->getRepository(Role::getClassName());
		$role = $roleRepo->findOneByName(Role::CANDIDATE);

		$count = 0;
		$importedUsers = $importedUserRepo->findAll();
		foreach ($importedUsers as $importedUser) {
			$user = $userRepo->findOneByMail($importedUser->mail);
			if (!$user) {
				$user = new User($importedUser->mail);
				$user->createdByAdmin = TRUE;

				$user->addRole($role);

				$this->loadUserData($user, $importedUser);

				$userRepo->save($user);
				$count++;
			}
		}
		return $count;
	}

	private function loadUserData(User &$user, ImportedUser $imported, $loadAll = TRUE)
	{
		$change = FALSE;
		if ($imported->firstname && ($loadAll || !$user->person->firstname)) {
			$user->person->firstname = $imported->firstname;
			$change = TRUE;
		}
		if ($imported->surname && ($loadAll || !$user->person->surname)) {
			$user->person->surname = $imported->surname;
			$change = TRUE;
		}
		if ($imported->linkedinLink && ($loadAll || !$user->person->linkedinLink)) {
			$user->person->linkedinLink = $imported->linkedinLink;
			$this->parseImageFromLinkedin($user);
			$change = TRUE;
		}
		if ($imported->country && ($loadAll || !$user->person->address || !$user->person->address->country)) {
			if (!$user->person->address) {
				$user->person->address = new Address();
			}
			switch ($imported->country) {
				case 'Portugal':
				case 'Portugal-UK':
				case 'Portugal/Germany':
					$user->person->address->country = 23;
					break;
				default:
					throw new InternalException('Unexpected "country" value: "' . $imported->country . '"');
			}
			$change = TRUE;
		}
		if ($imported->coreSkill && ($loadAll || !count($user->person->candidate->cv->skillKnows))) {
			$this->addSkill($user->person->candidate->cv, $imported->coreSkill);
			$cvRepo = $this->em->getRepository(Cv::getClassName());
			$cvRepo->save($user->person->candidate->cv);
			$change = TRUE;
		}
		if ($imported->otherSkill && ($loadAll || !count($user->person->candidate->cv->skillKnows))) {
			$this->addSkill($user->person->candidate->cv, $imported->otherSkill);
			$cvRepo = $this->em->getRepository(Cv::getClassName());
			$cvRepo->save($user->person->candidate->cv);
			$change = TRUE;
		}
		return $change;
	}

	private function parseImageFromLinkedin(User $user)
	{

	}

	private function addSkill(Cv $cv, $skillName)
	{
		switch ($skillName) {
			case 'FULL-STACK':
				$skillName = '...';
				break;
		}
		$skillRepo = $this->em->getRepository(Skill::getClassName());
		$skill = $skillRepo->findOneByName($skillName);

		if ($skill) {
			$skillLevel = $this->em->getRepository(SkillLevel::getClassName())->find(SkillLevel::NOT_DEFINED);
			$skillKnow = new SkillKnow();
			$skillKnow->skill = $skill;
			$skillKnow->level = $skillLevel;
			$skillKnow->years = 0;
			$skillKnow->cv = $cv;
			$cv->skillKnow = $skillKnow;
		} else {
			throw new InternalException('Unexpected "skill" value: "' . $skillName . '"');
		}
	}

	/** @return CsvUserImport */
	public function createComponentCsvImportForm()
	{
		$control = $this->iCsvUserImportFactory->create();
		$control->onSuccess = function (array $importedUsers) {
			$count = count($importedUsers);
			if ($count) {
				$message = $this->translator->translate('%count% users was successfully updated.', $count, ['count' => $count]);
				$type = 'success';
			} else {
				$message = $this->translator->translate('No user was updated');
				$type = 'warning';
			}
			$this->flashMessage($message, $type);
		};
		$control->onFail = function ($message) {
			$this->flashMessage($message, 'danger');
		};
		$control->onDone = function () {
			$this->redirect('this');
		};
		return $control;
	}

}
