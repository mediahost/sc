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

	$router[] = $adminRouter = new RouteList('Admin');
	$adminRouter[] = new Route("admin/<presenter>/<action>[/<id>]", array(
	    'presenter' => "Dashboard",
	    'action' => "default",
	    'id' => NULL,
	));

	$router[] = $adminRouter = new RouteList('Front');
	$adminRouter[] = new Route("<presenter>/<action>[/<id>]", array(
	    'presenter' => "Homepage",
	    'action' => "default",
	    'id' => NULL,
	));

	return $router;
    }

}
