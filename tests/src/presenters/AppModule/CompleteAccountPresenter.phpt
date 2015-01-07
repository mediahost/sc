<?php

namespace Test\Presenters\AppModule;

use Tester\Assert;
use Tester\DomQuery;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: CompleteAccountPresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompleteAccountPresenterTest extends BasePresenter
{

	public function setUp()
	{
		parent::setUp();
		$this->tester->init('App:CompleteAccount');
	}

	public function testUnlogged()
	{
		$response = $this->tester->test('default');
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
	}

	public function testForbidden()
	{
		$this->loginCandidate();
		$response = $this->tester->test('default');
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
	}

	public function testDefault()
	{
		$this->loginSigned();
		$response = $this->tester->testActionGet('default');

		$html = (string) $response->getSource();
		$dom = DomQuery::fromHtml($html);

		Assert::true($dom->has('html'));
	}

}

$test = new CompleteAccountPresenterTest($container);
$test->run();
