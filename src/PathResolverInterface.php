<?php

namespace Mano\AutotestBundle;

use Symfony\Component\Routing\Route;

/**
 * Resolve the path and set it to the RouteDecorator (RouteDecorator::setResolvedPath)
 */
interface PathResolverInterface
{
    public function resolve(RouteDecorator $route): void;
}
