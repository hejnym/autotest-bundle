<?php

namespace Mano\AutotestBundle\Tests;

use Mano\AutotestBundle\PathResolverInterface;
use Mano\AutotestBundle\RouteDecorator;
use Symfony\Component\Routing\Route;

class TestPathResolver implements PathResolverInterface
{
    public function resolve(Route $route): ?RouteDecorator
    {
        $routeDecorator = new RouteDecorator($route);
        $routeDecorator->setResolvedPath('foo');
        return $routeDecorator;
    }
}
