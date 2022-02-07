<?php

namespace Mano\AutotestBundle;

use Symfony\Component\Routing\Route;

/**
 * Resolve the path and set it (RouteDecorator::setResolvedPath)
 * or comment why is not resolved (RouteDecorator::setResolverComment)
 */
interface PathResolverInterface
{
    public function resolve(RouteDecorator $route): void;
}
