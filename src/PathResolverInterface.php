<?php

namespace Mano\AutotestBundle;

use Symfony\Component\Routing\Route;

/**
 * Resolve the path and set it to the RouteDecorator
 */
interface PathResolverInterface
{
    public function resolve(Route $route): ?RouteDecorator;
}
