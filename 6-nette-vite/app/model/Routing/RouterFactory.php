<?php declare(strict_types = 1);

namespace App\Model\Routing;

use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\Routing\Router;

final class RouterFactory
{

	public static function createRouter(): Router
	{
		$router = new RouteList();
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Home:default');

		return $router;
	}

}
