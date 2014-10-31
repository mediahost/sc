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
		$steps = preg_quote(SignPresenter::STEP1) . '|' . preg_quote(SignPresenter::STEP2);
		$frontRouter[] = new Route('sign/in/<role (' . $roles . ')>', [
			'presenter' => 'Sign',
			'action' => 'in',
			'step' => NULL,
		]);

		$frontRouter[] = new Route('registration/<role (' . $roles . ')>', [
			'presenter' => 'Sign',
			'action' => 'up',
			'step' => NULL,
		]);
		$frontRouter[] = new Route('registration/step1', [
			'presenter' => 'Sign',
			'action' => 'up',
			'role' => NULL,
			'step' => SignPresenter::STEP1,
		]);
		$frontRouter[] = new Route('registration/step2', [
			'presenter' => 'Sign',
			'action' => 'up',
			'role' => NULL,
			'step' => SignPresenter::STEP2,
		]);
		$frontRouter[] = new Route('registration/step3', [
			'presenter' => 'Sign',
			'action' => 'up',
			'role' => NULL,
			'step' => SignPresenter::STEP3,
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
