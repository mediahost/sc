<?php

namespace Test\Extensions\Settings\Model\Service;

use App\Extensions\Settings\Model\Storage\DefaultSettingsStorage;
use Nette\DI\Container;
use Test\ParentTestCase;

/**
 * Parent Service
 *
 * @author Petr Poupě
 */
abstract class ParentService extends ParentTestCase
{
	
	/** @var DefaultSettingsStorage */
	protected $defaultSettings;

	public function __construct(Container $container)
	{
		parent::__construct($container);
		$this->defaultSettings = new DefaultSettingsStorage;
	}

}
