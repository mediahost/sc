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

	/** @var \Nette\Security\User @inject */
	public $user;

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

		$grid->addColumnText('name', 'Job Title')
			->setCustomRender(function (Job $item) {
				return Html::el('a')
					->setHref($this->presenter->link('Job:view', [
						'id' => $item->id,
					]))->setHtml($item);
			})
			->setTruncate(20)
			->setSortable()
			->setFilterText()
			->setSuggestion();

		if ($this->user->isAllowed('job', 'accountManager')) {
			$userRepo = $this->em->getRepository(User::getClassName());
			$accountManagers = $userRepo->findAccountManagers();
			$grid->addColumnText('accountManager', 'Job Admin')
				->setSortable()
				->setFilterSelect([NULL => '--- any ---'] + $accountManagers);
		}

		$companyRepo = $this->em->getRepository(Company::getClassName());
		$companies = $companyRepo->findPairs('name');
		if (!$this->company) {
			$grid->addColumnText('company', 'Company')
				->setSortable()
				->setFilterSelect([NULL => '--- any ---'] + $companies);
		}

		if ($this->user->isAllowed('job', 'showNotMatched')) {
			$grid->addColumnText('applied', 'Requested')
				->setCustomRender(function (Job $item) {
					return Html::el('a class="btn btn-xs"')
						->setHref($this->presenter->link('Job:view', [
							'id' => $item->id,
							'state' => Match::STATE_APPLIED_ONLY,
						]))->setHtml($item->getAppliedCount());
				});
			$grid->getColumn('applied')->getHeaderPrototype()->setWidth('80px');
			$grid->getColumn('applied')->getHeaderPrototype()->class[] = 'center';
			$grid->getColumn('applied')->getCellPrototype()->class[] = 'center';

			$grid->addColumnText('invited', 'Invited')
				->setCustomRender(function (Job $item) {
					return Html::el('a class="btn btn-xs"')
						->setHref($this->presenter->link('Job:view', [
							'id' => $item->id,
							'state' => Match::STATE_INVITED_ONLY,
						]))->setHtml($item->getInvitedCount());
				});
			$grid->getColumn('invited')->getHeaderPrototype()->setWidth('80px');
			$grid->getColumn('invited')->getHeaderPrototype()->class[] = 'center';
			$grid->getColumn('invited')->getCellPrototype()->class[] = 'center';
		}

		$grid->addColumnText('matched', 'Applied')
			->setCustomRender(function (Job $item) {
				return Html::el('a class="btn btn-xs"')
					->setHref($this->presenter->link('Job:view', [
						'id' => $item->id,
						'state' => NULL,
					]))->setHtml($item->getMatchedCount(FALSE));
			});
		$grid->getColumn('matched')->getHeaderPrototype()->setWidth('80px');
		$grid->getColumn('matched')->getHeaderPrototype()->class[] = 'center';
		$grid->getColumn('matched')->getCellPrototype()->class[] = 'center';

		$grid->addColumnText('accepted', 'Shortlisted')
			->setCustomRender(function (Job $item) {
				return Html::el('a class="btn btn-xs"')
					->setHref($this->presenter->link('Job:view', [
						'id' => $item->id,
						'state' => Match::STATE_ACCEPTED_ONLY,
					]))->setHtml($item->getAcceptedCount());
			});
		$grid->getColumn('accepted')->getHeaderPrototype()->setWidth('80px');
		$grid->getColumn('accepted')->getHeaderPrototype()->class[] = 'center';
		$grid->getColumn('accepted')->getCellPrototype()->class[] = 'center';

		$grid->addColumnText('rejected', 'Rejected')
			->setCustomRender(function (Job $item) {
				return Html::el('a class="btn btn-xs"')
					->setHref($this->presenter->link('Job:view', [
						'id' => $item->id,
						'state' => Match::STATE_REJECTED,
					]))->setHtml($item->getRejectedCount());
			});
		$grid->getColumn('rejected')->getHeaderPrototype()->setWidth('80px');
		$grid->getColumn('rejected')->getHeaderPrototype()->class[] = 'center';
		$grid->getColumn('rejected')->getCellPrototype()->class[] = 'center';

		foreach (Match::getStates() as $id => $name) {
			$grid->addColumnText('state' . $id, $name)
				->setCustomRender(function (Job $item) use ($id) {
					return Html::el('a class="btn btn-xs"')
						->setHref($this->presenter->link('Job:view', [
							'id' => $item->id,
							'state' => $id,
						]))->setHtml($item->getInStateCount($id));
				});
			$grid->getColumn('state' . $id)->getHeaderPrototype()->setWidth('100px');
			$grid->getColumn('state' . $id)->getHeaderPrototype()->class[] = 'center';
			$grid->getColumn('state' . $id)->getCellPrototype()->class[] = 'center';
		}

		$grid->addActionHref('edit', 'Edit', 'Job:edit')
			->setIcon('fa fa-edit');

		$grid->addActionHref('delete', 'Delete')
			->setIcon('fa fa-trash-o')
			->setConfirm(function ($item) {
				$message = $this->translator->translate('Are you sure you want to delete \'%s\'?');
				return sprintf($message, (string)$item);
			})
			->getElementPrototype()->class[] = 'red';
		$grid->setActionWidth('270px');

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
