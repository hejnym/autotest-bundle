<?php

namespace Mano\AutotestBundle;

use Symfony\Component\Routing\Route;

/**
 * Resolve the path and set it back on RouteDecorator using setResolvedPath method.
 * If route could not be automatically resolved, add comment why is not resolved (setResolverComment)
 * for debugging purposes.
 */
interface PathResolverInterface
{
    public function resolve(RouteDecorator $route): RouteDecorator;
}
