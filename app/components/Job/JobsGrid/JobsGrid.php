<?php

namespace App\Components\Grids\Job;

use App\Components\BaseControl;
use App\Extensions\Grido\BaseGrid;
use App\Model\Entity\Company;
use App\Model\Entity\Job;
use App\Model\Entity\Match;
use App\Model\Entity\User;
use App\Model\Facade\UserFacade;
use Grido\DataSources\Doctrine;
use Nette\Utils\Html;
use Nette\Utils\Strings;

class JobsGrid extends BaseControl
{

	/** @var Company */
	private $company;

	protected function createComponentGrid()
	{
		$grid = new BaseGrid();
		$grid->setTranslator($this->translator);
		$grid->setTheme(BaseGrid::THEME_SUPR);

		$grid->model = $this->getModel();

		$grid->setDefaultSort([
			'id' => 'DESC',
			'name' => 'ASC',
		]);

		$grid->addColumnNumber('id', 'Id')
			->setSortable()
			->setFilterNumber();
		$grid->getColumn('id')->getHeaderPrototype()->setWidth('5%');

		$grid->addColumnText('name', 'Name')
			->setSortable()
			->setFilterText()
			->setSuggestion();

		$userRepo = $this->em->getRepository(User::getClassName());
		$accountManagers = $userRepo->findAccountManagers();
		$grid->addColumnText('accountManager', 'Account Manager')
			->setSortable()
			->setFilterSelect([NULL => '--- any ---'] + $accountManagers);

		$companyRepo = $this->em->getRepository(Company::getClassName());
		$companies = $companyRepo->findPairs('name');
		if (!$this->company) {
			$grid->addColumnText('company', 'Company')
				->setSortable()
				->setFilterSelect([NULL => '--- any ---'] + $companies);
		}

		$grid->addColumnText('matched', 'Matched')
			->setCustomRender(function (Job $item) {
				return Html::el('a class="btn btn-xs"')
					->setHref($this->presenter->link('Job:candidates', [
						'id' => $item->id,
						'state' => Match::STATE_MATCHED,
					]))->setHtml($item->getMatchedCount());
			});
		$grid->getColumn('matched')->getHeaderPrototype()->setWidth('100px');
		$grid->getColumn('matched')->getHeaderPrototype()->class[] = 'center';
		$grid->getColumn('matched')->getCellPrototype()->class[] = 'center';

		$grid->addColumnText('accepted', 'Accepted')
			->setCustomRender(function (Job $item) {
				return Html::el('a class="btn btn-xs"')
					->setHref($this->presenter->link('Job:candidates', [
						'id' => $item->id,
						'state' => Match::STATE_ACCEPTED,
					]))->setHtml($item->getAcceptedCount());
			});
		$grid->getColumn('accepted')->getHeaderPrototype()->setWidth('100px');
		$grid->getColumn('accepted')->getHeaderPrototype()->class[] = 'center';
		$grid->getColumn('accepted')->getCellPrototype()->class[] = 'center';

		foreach (Match::getStates() as $id => $name) {
			$grid->addColumnText('state' . $id, $name)
				->setCustomRender(function (Job $item) use ($id) {
					return Html::el('a class="btn btn-xs"')
						->setHref($this->presenter->link('Job:candidates', [
							'id' => $item->id,
							'state' => $id,
						]))->setHtml($item->getInStateCount($id));
				});
			$grid->getColumn('state' . $id)->getHeaderPrototype()->setWidth('100px');
			$grid->getColumn('state' . $id)->getHeaderPrototype()->class[] = 'center';
			$grid->getColumn('state' . $id)->getCellPrototype()->class[] = 'center';
		}

		$grid->addActionHref('view', 'View', 'Job:view')
			->setIcon('fa fa-eye');

		$grid->addActionHref('candidates', 'Candidates', 'Job:candidates')
			->setIcon('fa fa-users');

		$grid->addActionHref('edit', 'Edit', 'Job:edit')
			->setIcon('fa fa-edit');

		$grid->addActionHref('delete', 'Delete')
			->setIcon('fa fa-trash-o')
			->setConfirm(function ($item) {
				$message = $this->translator->translate('Are you sure you want to delete \'%s\'?');
				return sprintf($message, (string)$item);
			})
			->getElementPrototype()->class[] = 'red';

		$grid->setActionWidth("20%");

		return $grid;
	}

	public function getModel()
	{
		$jobRepo = $this->em->getRepository(Job::getClassName());
		$qb = $jobRepo->createQueryBuilder('j')
			->select('j, c')
			->innerJoin('j.company', 'c');
		if ($this->company) {
			$qb->where('j.company = :company')
				->setParameter('company', $this->company);
		}
		$model = new Doctrine($qb, [
			'company' => 'c'
		]);
		return $model;
	}

	public function setCompany(Company $company)
	{
		$this->company = $company;
		return $this;
	}

}

interface IJobsGridFactory
{

	/** @return JobsGrid */
	function create();
}
