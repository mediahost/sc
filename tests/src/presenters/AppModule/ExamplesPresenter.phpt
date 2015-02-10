<?php

namespace Test\Presenters\AppModule;

use Tester\Assert;
use Tester\DomQuery;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: ExamplesPresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class ExamplesPresenterTest extends BasePresenter
{

	protected function setUp()
	{
		parent::setUp();
		$this->tester->init('App:Examples');
	}

	public function testUnlogged()
	{
		$response = $this->tester->test('form');
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
	}

	public function testForbidden()
	{
		$this->loginAdmin();
		Assert::exception(function() {
			$this->tester->test('form');
		}, 'Nette\Application\ForbiddenRequestException');
	}

	public function testDefault()
	{
		$this->loginSuperadmin();
		$response = $this->tester->testActionGet('form');

		$html = (string) $response->getSource();
		$dom = @DomQuery::fromHtml($html);

		Assert::true($dom->has('html'));
	}

}

$test = new ExamplesPresenterTest($container);
$test->run();
