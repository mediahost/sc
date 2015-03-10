<?php

namespace Test\Presenters\AppModule;

use Nette\Application\Responses\RedirectResponse;
use Tester\Assert;
use Tester\DomQuery;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: SkillCategoriesPresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class SkillCategoriesPresenterTest extends AppBasePresenter
{

	protected function setUp()
	{
		parent::setUp();
		$this->openPresenter('App:Skills');
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

	public function testDefault()
	{
		$this->loginAdmin();
		$response = $this->runPresenterActionGet('default');

		$html = (string) $response->getSource();
		$dom = DomQuery::fromHtml($html);

		Assert::true($dom->has('html'));
	}

}

$test = new SkillCategoriesPresenterTest($container);
$test->run();
