<?php

namespace Mano\AutotestBundle;

use Symfony\Component\Routing\Route;

/**
 * Get path of the route as a string
 */
interface PathResolverInterface
{
    public function resolve(Route $route): ?RouteDecorator;
}
