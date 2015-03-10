<?php

namespace Test\Presenters\AppModule;

use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;
use Tester\DomQuery;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: CandidatePresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class CandidatePresenterTest extends AppBasePresenter
{

	protected function setUp()
	{
		parent::setUp();
		$this->openPresenter('App:Candidate');
	}

	public function testUnlogged()
	{
		$response = $this->runPresenterActionGet('default');
		Assert::type(RedirectResponse::class, $response);
	}

	public function testForbidden()
	{
		$this->loginCompany();
		Assert::exception(function() {
			$this->runPresenterActionGet('default');
		}, 'Nette\Application\ForbiddenRequestException');
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

$test = new CandidatePresenterTest($container);
$test->run();
