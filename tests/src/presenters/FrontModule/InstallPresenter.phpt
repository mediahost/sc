<?php

namespace Test\Presenters\FrontModule;

use App\Helpers;
use Nette\DI\Container;
use Test\Presenters\BasePresenter;
use Tester\Assert;
use Tester\DomQuery;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: InstallPresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class InstallPresenterTest extends BasePresenter
{

	/** @var string */
	private $installDir;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->installDir = $this->container->getParameters()['tempDir'] . 'install/';
	}

	public function setUp()
	{
		parent::setUp();
		$this->updateSchema();

		$this->tester->init('Front:Install');
		if (!is_dir($this->installDir)) {
			mkdir($this->installDir);
		}
	}

	public function tearDown()
	{
		parent::tearDown();
		$this->dropSchema();

		Helpers::delTree($this->installDir);
	}

	public function testRenderDefault()
	{
		$response = $this->tester->testActionGet('default', ['printHtml' => TRUE]);
		$html = (string) $response->getSource();
		$dom = DomQuery::fromHtml($html);
		Assert::true($dom->has('html'));
		Assert::true($dom->has('body'));
	}

}

$test = new InstallPresenterTest($container);
$test->run();
