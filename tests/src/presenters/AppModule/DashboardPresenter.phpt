<?php

namespace Test\Presenters\AppModule;

use Tester\Assert;
use Tester\DomQuery;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: DashboardPresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class DashboardPresenterTest extends BasePresenter
{

	protected function setUp()
	{
		parent::setUp();
		$this->tester->init('App:Dashboard');
	}

	public function testUnlogged()
	{
		$response = $this->tester->test('default');
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
	}

	public function testOnlySigned()
	{
		$this->loginSigned();
		$response = $this->tester->test('default');
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
	}

	public function testDefault()
	{
		$this->loginCandidate();
		$response = $this->tester->test('default');
		
		$html = (string) $response->getSource();
		$dom = DomQuery::fromHtml($html);

		Assert::true($dom->has('html'));
	}

}

$test = new DashboardPresenterTest($container);
$test->run();
