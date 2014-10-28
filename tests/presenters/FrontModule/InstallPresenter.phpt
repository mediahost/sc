<?php

namespace Test\Presenters\FrontModule;

use Nette,
	Tester;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: InstallPresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class InstallPresenterTest extends Tester\TestCase
{

	/** @var Nette\DI\Container */
	private $container;

	/** @var Presenter */
	private $tester;

	/** @var string */
	private $installDir;

	function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
		$this->container->callInjects($this);
		$this->installDir = $this->container->getParameters()['tempDir'] . 'install/';
		$this->tester = new \Test\Presenters\Presenter($container);
	}

	public function setUp()
	{
		$this->tester->init('Front:Install');
		mkdir($this->installDir);
	}

	public function tearDown()
	{
		\App\Helpers::delTree($this->installDir);
	}

	public function testRenderDefault()
	{
		$response = $this->tester->testAction('default');
		
		$html = (string) $response->getSource();
		$dom = \Tester\DomQuery::fromHtml($html);
		\Tester\Assert::true($dom->has('html'));
		\Tester\Assert::true($dom->has('body'));
	}

}

$test = new InstallPresenterTest($container);
$test->run();
