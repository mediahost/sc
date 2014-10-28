<?php

namespace Test\Presenters\FrontModule;

use Nette,
	Tester;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: HomepagePresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class HomepagePresenterTest extends Tester\TestCase
{

	/** @var Presenter */
	private $tester;

	function __construct(Nette\DI\Container $container)
	{
		$this->tester = new \Test\Presenters\Presenter($container);
	}

	public function setUp()
	{
		$this->tester->init('Front:Homepage');
	}

	public function testRenderDefault()
	{
		$response = $this->tester->testAction('default');
		
		$html = (string) $response->getSource();
		$dom = \Tester\DomQuery::fromHtml($html);
		\Tester\Assert::true($dom->has('html'));
		\Tester\Assert::true($dom->has('head'));
		\Tester\Assert::true($dom->has('title'));
		\Tester\Assert::true($dom->has('body'));
	}

}

$test = new HomepagePresenterTest($container);
$test->run();
