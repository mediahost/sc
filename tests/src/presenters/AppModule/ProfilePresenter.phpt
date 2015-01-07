<?php

namespace Test\Presenters\AppModule;

use Tester\Assert;
use Tester\DomQuery;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: ProfilePresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class ProfilePresenterTest extends BasePresenter
{

	public function setUp()
	{
		parent::setUp();
		$this->tester->init('App:Profile');
	}

	public function testUnlogged()
	{
		$response = $this->tester->test('default');
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
	}

	public function testDefault()
	{
		$this->loginCandidate();
		$response = $this->tester->testActionGet('default');

		$html = (string) $response->getSource();
		$dom = DomQuery::fromHtml($html);

		Assert::true($dom->has('html'));
	}

}

$test = new ProfilePresenterTest($container);
$test->run();
