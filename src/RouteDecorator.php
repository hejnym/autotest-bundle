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

    /** @var string */
    protected $routeName;

    public function __construct(Route $route, string $routeName)
    {
        $this->route = $route;
        $this->routeName = $routeName;
    }

    public function getResolvedPath(): ?string
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

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }
}
