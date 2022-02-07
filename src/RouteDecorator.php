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

    /**
     * Add comment about why the route could not be resolved
     * @var string
     */
    protected $resolverComment;

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

    public function getResolverComment(): ?string
    {
        return $this->resolverComment;
    }

    public function setResolverComment(string $resolverComment): void
    {
        $this->resolverComment = $resolverComment;
    }
}
