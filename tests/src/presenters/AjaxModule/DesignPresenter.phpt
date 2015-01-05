<?php

namespace Test\Presenters\FrontModule;

use App\Extensions\Installer;
use Test\Presenters\Presenter;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: DesignPresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class DesignPresenterTest extends BasePresenter
{

	/** @var Installer @inject */
	public $installer;

	public function setUp()
	{
		parent::setUp();
		$this->updateSchema();
		$this->installer->install();
		$this->tester->init('Ajax:Design');
	}

	public function tearDown()
	{
		$this->logout();
		$this->dropSchema();
	}

	public function testRenderSetColor()
	{
		$this->loginAdmin();
		$color = 'default';
		$response = $this->tester->testAction('setColor', Presenter::METHOD_GET, ['color' => $color]);

		$json = (string) $response->getSource();
		$arrayResponse = json_decode($json);
		Assert::count(1, (array) $arrayResponse);
		Assert::same($color, $arrayResponse->success->color);
	}

	public function testRenderSetColorFail()
	{
		$this->loginAdmin();
		$response = $this->tester->testAction('setColor', Presenter::METHOD_GET, ['color' => 'blue']);

		$json = (string) $response->getSource();
		$arrayResponse = json_decode($json);
		Assert::count(1, (array) $arrayResponse);
		Assert::same('This color isn\'t supported.', $arrayResponse->error);
	}

	public function testRenderSetColorUnlogged()
	{
		$color = 'default';
		$response = $this->tester->testAction('setColor', Presenter::METHOD_GET, ['color' => $color]);

		$json = (string) $response->getSource();
		$arrayResponse = json_decode($json);
		Assert::count(1, (array) $arrayResponse);
		Assert::same('You aren\'logged in.', $arrayResponse->error);
	}

	public function testRenderSetSidebarClosed()
	{
		$this->loginAdmin();

		$response = $this->tester->testAction('setSidebarClosed', Presenter::METHOD_GET, ['value' => TRUE]);
		$json = (string) $response->getSource();
		$arrayResponse = json_decode($json);
		Assert::count(1, (array) $arrayResponse);
		Assert::true($arrayResponse->success->sidebarClosed);
	}

	public function testRenderSetSidebarClosedUnlogged()
	{
		$response = $this->tester->testAction('setSidebarClosed', Presenter::METHOD_GET, ['value' => TRUE]);
		$json = (string) $response->getSource();
		$arrayResponse = json_decode($json);
		Assert::count(1, (array) $arrayResponse);
		Assert::same('You aren\'logged in.', $arrayResponse->error);
	}

	public function testRenderSetLayout()
	{
		$this->loginAdmin();

		$response = $this->tester->testAction('setLayout', Presenter::METHOD_GET, [
			'layoutOption' => 'boxed',
			'sidebarOption' => 'fixed',
			'headerOption' => 'fixed',
			'footerOption' => 'fixed',
			'sidebarPosOption' => 'right',
			'sidebarStyleOption' => 'light',
			'sidebarMenuOption' => 'hover',
		]);
		$json = (string) $response->getSource();
		$arrayResponse = json_decode($json);
		Assert::count(1, (array) $arrayResponse);
		Assert::count(7, (array) $arrayResponse->success);
		Assert::true($arrayResponse->success->layoutBoxed);
		Assert::true($arrayResponse->success->sidebarFixed);
		Assert::true($arrayResponse->success->headerFixed);
		Assert::true($arrayResponse->success->footerFixed);
		Assert::true($arrayResponse->success->sidebarReversed);
		Assert::true($arrayResponse->success->sidebarMenuLight);
		Assert::true($arrayResponse->success->sidebarMenuHover);
	}

	public function testRenderSetLayoutUnlogged()
	{
		$response = $this->tester->testAction('setLayout', Presenter::METHOD_GET, []);
		$json = (string) $response->getSource();
		$arrayResponse = json_decode($json);
		Assert::count(1, (array) $arrayResponse);
		Assert::same('You aren\'logged in.', $arrayResponse->error);
	}

}

$test = new DesignPresenterTest($container);
$test->run();
