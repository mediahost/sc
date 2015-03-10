<?php

namespace Test;

use Tester\Assert;
use Tracy\Debugger;

abstract class StressTestCase extends DbTestCase
{

	const TIME_ALL = 'all_time';

	/** @var int */
	protected $time;

	private $limitTime;

	private $limitDescription;

	protected function setLimitTime($time, $description = NULL)
	{
		$this->limitTime = $time;
		$this->limitDescription = $description;
	}

	protected function setUp()
	{
		parent::setUp();
		Debugger::timer(self::TIME_ALL);
	}

	protected function tearDown()
	{
		$this->time = Debugger::timer(self::TIME_ALL);
		if ($this->limitTime) {
			Debugger::barDump($this->time, $this->limitDescription . " Time: " . $this->limitTime);
			Assert::true($this->time <= $this->limitTime);
		}
		parent::tearDown();
	}

}
