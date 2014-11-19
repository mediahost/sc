<?php

namespace App;

use App\FrontModule\Presenters\SignPresenter;
use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Utils\Strings;

/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();

		$router[] = new Route('index.php', 'Front:Default:default', Route::ONE_WAY);

		$router[] = $adminRouter = new RouteList('App');
		$router[] = $frontRouter = new RouteList('Front');

		// <editor-fold defaultstate="expanded" desc="App">

		$adminRouter[] = new Route('app/<presenter>/<action>[/<id>]', [
			'presenter' => 'Home',
			'action' => 'default',
			'id' => NULL,
		]);

		// </editor-fold>
		// <editor-fold defaultstate="expanded" desc="Front">

		$roles = preg_quote(SignPresenter::ROLE_CANDIDATE) . '|' . preg_quote(SignPresenter::ROLE_COMPANY);
		$frontRouter[] = new Route('<presenter>/<action (in|up)>[/<role (' . $roles . ')>]', [
			'presenter' => 'Sign'
		]);

		$frontRouter[] = new Route('<presenter>/<action>[/<id>]', [
			'presenter' => 'Homepage',
			'action' => 'default',
			'id' => NULL,
		]);

		// </editor-fold>

		return $router;
	}

}
