<?php

namespace Test\Presenters\FotoModule;

use Nette\Utils\Image;
use Test\Presenters\BasePresenter;
use Test\Presenters\Presenter;
use Tester\Assert;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: FotoPresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class FotoPresenterTest extends BasePresenter
{

	protected function setUp()
	{
		parent::setUp();
		$this->tester->init('Foto:Foto');
	}

	public function testFoto()
	{
		$response = $this->tester->test('default', Presenter::METHOD_GET, ['size' => '200-200', 'name' => 'person/default.png']);
		Assert::same(NULL, $response);
		// TODO: Why response is NULL when test run, but in browser its print image - resolve it
		// Todo: after resolving - compare with right image
	}

}

$test = new FotoPresenterTest($container);
$test->run();
