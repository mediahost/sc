<?php

namespace App\ApiModule\Presenters;

use Nette\Application\ForbiddenRequestException;
use Nette\Http\Request;

abstract class BasePresenter extends \App\BaseModule\Presenters\BasePresenter
{

	/** @var Request @inject */
	public $request;

	protected function startup()
	{
		parent::startup();
		$ip = $this->request->getRemoteAddress();
		if (!$this->settings->modules->api->enabled) {
			throw new ForbiddenRequestException('Api module is not allowed.');
		}
		if (!in_array($ip, (array)$this->settings->modules->api->allowedIps)) {
			$allowed = implode(', ', (array)$this->settings->modules->api->allowedIps);
			throw new ForbiddenRequestException('Your IP (' . $ip . ') is not allowed. Allowed are [' . $allowed . ']');
		}
	}
}
