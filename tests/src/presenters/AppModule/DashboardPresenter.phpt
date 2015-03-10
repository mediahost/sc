<?php

namespace Test\Presenters\AppModule;

use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;
use Tester\DomQuery;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: DashboardPresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class DashboardPresenterTest extends AppBasePresenter
{

	protected function setUp()
	{
		parent::setUp();
		$this->openPresenter('App:Dashboard');
	}

	public function testUnlogged()
	{
		$response = $this->runPresenterActionGet('default');
		Assert::type(RedirectResponse::class, $response);
	}

	public function testOnlySigned()
	{
		$this->loginSigned();
		$response = $this->runPresenterActionGet('default');
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
	}

	public function testDefault()
	{
		$this->loginCandidate();
		$response = $this->runPresenterActionGet('default');
		
		$html = (string) $response->getSource();
		$dom = DomQuery::fromHtml($html);

		Assert::true($dom->has('html'));
	}

}

$test = new DashboardPresenterTest($container);
$test->run();
