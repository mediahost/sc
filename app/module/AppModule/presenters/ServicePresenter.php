<?php

namespace App\AppModule\Presenters;

use App\Extensions\Installer;
use App\Model\Entity\Company;
use App\Model\Entity\CompanyRole;
use App\Model\Entity\Job;
use App\Model\Entity\Role;
use App\Model\Entity\Skill;
use App\Model\Entity\SkillCategory;
use App\Model\Facade\CompanyFacade;
use App\Model\Facade\RoleFacade;
use App\Model\Facade\UserFacade;
use Doctrine\ORM\Tools\SchemaTool;
use Kdyby\Doctrine\Connection;
use Kdyby\Doctrine\Helpers;
use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Tracy\Debugger;

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
	 * @privilege('importInitData')
	 */
	public function handleImportInitData()
	{
		$this->reinstall();
		$this->importDbAll();
		$this->flashMessage('Data was imported from SQL files', 'success');
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
		$this->flashMessage('DB was instaled', 'success');
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
		$this->flashMessage('DB was reinstaled', 'success');
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
		$this->flashMessage('Skills and cateories was succesfully created', 'success');
		$this->redirect('this');
	}

	/**
	 * @secured
	 * @resource('service')
	 * @privilege('createCandidates')
	 */
	public function handleCreateCandidates($candidateCnt)
	{
		$this->createCandidates($candidateCnt);
		$this->flashMessage('Candidates and their CVs was succesfully created', 'success');
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
		$this->flashMessage('Candidates and their CVs was succesfully created', 'success');
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
		return $this;
	}

	private function importDbSkills()
	{
		$this->importDbDataFromFile(realpath('./../tests/sql/skills.sql'));
		return $this;
	}

	/**
	 * Import all data for testing candidates (users, cvs)
	 * Required import skills (for cvs)
	 * @return self
	 */
	private function importDbCandidates()
	{
		$this->importDbDataFromFile(realpath('./../tests/sql/candidates.sql'));
		return $this;
	}

	/**
	 * Import all data for testing companies (users, companies, jobs)
	 * Required import skills (for jobs)
	 * @return self
	 */
	private function importDbCompanies()
	{
		$this->importDbDataFromFile(realpath('./../tests/sql/companies.sql'));
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

	private function createCandidates($candidateCnt)
	{
		$roleCandidate = $this->roleFacade->findByName(Role::CANDIDATE);
		// TODO: Start value to max ID
		$start = 0;
		for ($i = $start + 1; $i <= $start + $candidateCnt; $i++) {
			$this->userFacade->create("user{$i}@candidate.com", "user{$i}", $roleCandidate);
			// TODO: create CV
		}
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

}