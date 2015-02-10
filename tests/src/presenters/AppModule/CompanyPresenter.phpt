<?php

namespace Test\Presenters\AppModule;

use Tester\Assert;
use Tester\DomQuery;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: CompanyPresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyPresenterTest extends BasePresenter
{

	protected function setUp()
	{
		parent::setUp();
		$this->tester->init('App:Company');
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
		$this->loginCompany();
		$response = $this->tester->test('default');
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
	}

	public function testWrongCompany()
	{
		$this->loginCompany();
		$response = $this->tester->test('wrongCompany');

		$html = (string) $response->getSource();
		$dom = DomQuery::fromHtml($html);

		Assert::true($dom->has('html'));
	}

}

$test = new CompanyPresenterTest($container);
$test->run();
