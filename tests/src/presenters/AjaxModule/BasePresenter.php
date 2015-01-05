<?php

namespace Test\Presenters\AjaxModule;

use Nette\DI\Container;
use Test\ParentTestCase;
use Test\Presenters\Presenter;

/**
 * Parent presenter
 */
abstract class BasePresenter extends ParentTestCase
{

	/** @var Container */
	protected $container;

	/** @var Presenter */
	protected $tester;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->tester = new Presenter($container);
	}

}
