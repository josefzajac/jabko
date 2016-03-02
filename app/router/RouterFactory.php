<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    /**
     * @return Nette\Application\IRouter
     */
    public static function createRouter()
    {
        $router   = self::createBaseRouter();
        $router[] = self::createAdminRouter('Admin', 'admin');
        $router[] = self::createFrontendRouter('Frontend');

        return $router;
    }

    private static function createBaseRouter()
    {
        $router   = new RouteList();
        $router[] = self::route('test', 'Test:');

        return $router;
    }

    private static function createAdminRouter($moduleName, $routePrefix)
    {
        $router   = new RouteList($moduleName);
        $router[] = self::route($routePrefix . '/<presenter>/<action>[/<id>]', 'Homepage:default');

        return $router;
    }

    private static function createFrontendRouter($moduleName)
    {
        $router   = new RouteList($moduleName);
        $router[] = self::route('', 'Frontend:default');
        
        $router[] = self::route('notify-gopay', 'Payment:notifyGopay');

        $router[] = self::route('login', 'Login:login');
        $router[] = self::route('logout', 'Login:logout');
        $router[] = self::route('password-recovery', 'Login:passwordRecovery');

        return $router;
    }

    private static function route($mask, $metadata = [], $flags = 0)
    {
        return new Route('[<locale=cs cs|en>/]' . $mask, $metadata, $flags);
    }
}
