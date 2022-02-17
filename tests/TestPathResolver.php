<?php

namespace Mano\AutotestBundle\Tests;

use Mano\AutotestBundle\PathResolverInterface;
use Mano\AutotestBundle\RouteDecorator;
use Symfony\Component\Routing\Route;

class TestPathResolver implements PathResolverInterface
{
    public function resolve(RouteDecorator $route):RouteDecorator
    {
        $route->setResolvedPath('foo');
        return $route;
    }
}
