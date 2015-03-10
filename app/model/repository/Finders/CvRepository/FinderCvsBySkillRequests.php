<?php

namespace App\Model\Repository\Finders\CvRepository;

use App\Model\Entity\Skill;
use App\Model\Entity\SkillKnowRequest;
use App\Model\Repository\Finders\Finder;

class FinderCvsBySkillRequests extends Finder
{

	/** @var bool */
	private $joinLevelsTable = FALSE;

	/** @var array */
	private $orRequests = [];

	/** @var array */
	private $andRequests;

	protected function init()
	{
		$this->qb
				->innerJoin('e.skillKnows', 'k')
				->where('e.isDefault = :isDefault')
				->setParameter('isDefault', TRUE);
	}

	public function addRequest(SkillKnowRequest $request)
	{
		$this->andRequests = [];
		$this->addSkillEq($request->skill);
		$this->addLevelsEq($request);
		$this->addYearsEq($request);
		$this->buildAnds();
	}

	private function addSkillEq(Skill $skill)
	{
		$key = $this->getKeyName('skill');
		$this->andRequests[] = $this->getExpr()->eq('k.skill', ':' . $key);
		$this->setParameter($key, $skill);
	}

	private function addLevelsEq(SkillKnowRequest $request)
	{
		if ($request->hasOneLevel()) {
			$levelKey = $this->getKeyName('level');
			$this->andRequests[] = $this->getExpr()->eq('k.level', ':' . $levelKey);
			$this->setParameter($levelKey, $request->levelFrom);
		} else {
			$this->joinLevelsTable = TRUE;

			$levelFromKey = $this->getKeyName('levelFrom');
			$levelToKey = $this->getKeyName('levelTo');

			$expr1 = $this->getExpr()->gte('l.priority', ':' . $levelFromKey);
			$expr2 = $this->getExpr()->lte('l.priority', ':' . $levelToKey);
			$this->andRequests[] = $this->getExpr()->andX($expr1, $expr2);

			$this->setParameter($levelFromKey, $request->levelFrom);
			$this->setParameter($levelToKey, $request->levelFrom);
		}
	}

	private function addYearsEq(SkillKnowRequest $request)
	{
		if ($request->isYearsMatter()) {
			$keyFrom = $this->getKeyName('yearsFrom');
			$keyTo = $this->getKeyName('yearsTo');

			$expr1 = $this->getExpr()->gte('k.years', ':' . $keyFrom);
			$expr2 = $this->getExpr()->lte('k.years', ':' . $keyTo);
			$this->andRequests[] = $this->getExpr()->andX($expr1, $expr2);

			$this->setParameter($keyFrom, $request->yearsFrom);
			$this->setParameter($keyTo, $request->yearsTo);
		}
	}

	protected function build()
	{
		$this->buildJoins();
		$this->buildOrs();
	}

	private function buildJoins()
	{
		if ($this->joinLevelsTable) {
			$this->qb->innerJoin('k.level', 'l');
		}
	}

	private function buildAnds()
	{
		$this->orRequests[] = call_user_func_array([$this->getExpr(), 'andX'], $this->andRequests);
	}

	private function buildOrs()
	{
		$ors = call_user_func_array([$this->getExpr(), 'orX'], $this->orRequests);
		$this->qb->andWhere($ors);
	}

	private function getKeyName($name)
	{
		return $name . '_' . count($this->orRequests);
	}

}
