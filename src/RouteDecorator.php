<?php

namespace Mano\AutotestBundle;

use Symfony\Component\Routing\Route;

/**
 * Add properties (resolved path) to existing Route
 */
class RouteDecorator
{
    /** @var Route */
    protected $route;

    /** @var string */
    protected $resolvedPath;

    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    public function getResolvedPath(): string
    {
        return $this->resolvedPath;
    }

    public function setResolvedPath(string $resolvedPath): void
    {
        $this->resolvedPath = $resolvedPath;
    }

    /**
     * @return Route
     */
    public function getRoute(): Route
    {
        return $this->route;
    }
}
