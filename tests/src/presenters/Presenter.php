<?php

namespace Test\Presenters;

use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

/**
 * Presenter factory
 */
class Presenter extends TestCase
{

	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';

	/** @var Container */
	private $container;

	/** @var IPresenter */
	private $presenter;

	/** @var string */
	private $presName;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @param $presName string Fully qualified presenter name.
	 */
	public function init($presName)
	{
		$presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
		$this->presenter = $presenterFactory->createPresenter($presName);
		$this->presenter->autoCanonicalize = FALSE;
		$this->presName = $presName;
	}

	public function test($action, $method = self::METHOD_GET, $params = [], $post = [])
	{
		$params['action'] = $action;
		$request = new Request($this->presName, $method, $params, $post);
		$response = $this->presenter->run($request);
		return $response;
	}

	public function testAction($action, $method = self::METHOD_GET, $params = [], $post = [])
	{
		$response = $this->test($action, $method, $params, $post);

		Assert::true($response instanceof TextResponse);
		Assert::true($response->getSource() instanceof Template);

		return $response;
	}

	public function testActionGet($action, $params = [])
	{
		return $this->testAction($action, self::METHOD_GET, $params);
	}

	public function testForm($action, $method = self::METHOD_POST, $post = [])
	{
		$response = $this->test($action, $method, $post);

		Assert::true($response instanceof RedirectResponse);

		return $response;
	}

}
