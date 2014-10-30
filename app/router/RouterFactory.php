<?php

namespace App;

use Nette,
	Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;

/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return \Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();

		$router[] = new Route('index.php', 'Front:Default:default', Route::ONE_WAY);

		$router[] = $adminRouter = new RouteList('App');
		$adminRouter[] = new Route('app/<presenter>/<action>[/<id>]', [
			'presenter' => 'Home',
			'action' => 'default',
			'id' => NULL,
		]);

		$router[] = $frontRouter = new RouteList('Front');
		$frontRouter[] = new Route('sign-in/<role (admin|company|candidate)>', [
			'presenter' => 'NewSign',
			'action' => 'in'
		]);
//		$frontRouter[] = new Route('sign-up/<role (company|candidate)>', [
//			'presenter' => 'NewSign',
//			'action' => 'up'
//		]);
		$frontRouter[] = new Route('<presenter>/<action>[/<id>]', [
			'presenter' => 'Homepage',
			'action' => 'default',
			'id' => NULL,
		]);

		return $router;
	}

}
