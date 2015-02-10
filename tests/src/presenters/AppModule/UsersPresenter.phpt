<?php

namespace Test\Presenters\AppModule;

use Tester\Assert;
use Tester\DomQuery;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: UserPresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class UsersPresenterTest extends BasePresenter
{

	protected function setUp()
	{
		parent::setUp();
		$this->tester->init('App:Users');
	}

	public function testUnlogged()
	{
		$response = $this->tester->test('default');
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
	}

	public function testForbidden()
	{
		$this->loginCandidate();
		Assert::exception(function() {
			$this->tester->test('default');
		}, 'Nette\Application\ForbiddenRequestException');
	}

	public function testDefault()
	{
		$this->loginAdmin();
		$response = $this->tester->testActionGet('default');

		$html = (string) $response->getSource();
		$dom = DomQuery::fromHtml($html);

		Assert::true($dom->has('html'));
	}

}

$test = new UsersPresenterTest($container);
$test->run();
