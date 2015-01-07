<?php

namespace Test\Presenters\FrontModule;

use Test\Presenters\BasePresenter;
use Tester\Assert;
use Tester\DomQuery;

$container = require __DIR__ . '/../../bootstrap.php';

/**
 * TEST: SignPresenter
 *
 * @testCase
 * @phpVersion 5.4
 */
class SignPresenterTest extends BasePresenter
{

	public function setUp()
	{
		parent::setUp();
		$this->updateSchema();
		$this->installer->install();
		$this->tester->init('Front:Sign');
	}

	public function tearDown()
	{
		$this->logout();
		$this->dropSchema();
	}

	public function testRenderIn()
	{
		$response = $this->tester->testActionGet('in');

		$html = (string) $response->getSource();
		$dom = DomQuery::fromHtml($html);

		Assert::true($dom->has('html'));
		Assert::true($dom->has('head'));
		Assert::true($dom->has('title'));
		Assert::true($dom->has('body'));

		$form = $dom->find('form#frm-signIn-form');
		Assert::count(1, $form);
		
		$loginOptions = $dom->find('div.login-options');
		Assert::count(1, $loginOptions);
		
		$mail = $dom->find('input#frm-signIn-form-mail[type=text]');
		Assert::count(1, $mail);
		
		$password = $dom->find('input#frm-signIn-form-password[type=password]');
		Assert::count(1, $password);
		
		$remember = $dom->find('input#frm-signIn-form-remember[type=checkbox]');
		Assert::count(1, $remember);
		
		$button = $dom->find('button#frm-signIn-form-signIn[type=submit]');
		Assert::count(1, $button);
	}

	public function testRenderInLogged()
	{
		$this->loginAdmin();
		$response = $this->tester->test('in');
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
	}

}

$test = new SignPresenterTest($container);
$test->run();
