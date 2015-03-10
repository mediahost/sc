<?php

namespace Test\Presenters\AppModule;

use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;
use Tester\DomQuery;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: CompanyPresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class CompanyPresenterTest extends AppBasePresenter
{

	protected function setUp()
	{
		parent::setUp();
		$this->openPresenter('App:Company');
	}

	public function testUnlogged()
	{
		$response = $this->runPresenterActionGet('default');
		Assert::type(RedirectResponse::class, $response);
	}

	public function testForbidden()
	{
		$this->loginCandidate();
		Assert::exception(function() {
			$this->runPresenterActionGet('default');
		}, 'Nette\Application\ForbiddenRequestException');
	}

	public function testWithoutCompanyId()
	{
		$this->loginCompany();
		Assert::exception(function() {
			$this->runPresenterActionGet('default');
		}, 'Nette\Application\ForbiddenRequestException');
	}

	public function testWrongCompany()
	{
		$this->loginCompany();
		$response = $this->runPresenterActionGet('wrongCompany');

		$html = (string) $response->getSource();
		$dom = DomQuery::fromHtml($html);

		Assert::true($dom->has('html'));
	}

}

$test = new CompanyPresenterTest($container);
$test->run();
