<?php

namespace Mano\AutotestBundle;

use Mano\AutotestBundle\Exception\InvalidArgumentException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

final class Autotest
{
    /**
     * @var SimplePathResolver
     */
    private $pathResolver;
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var string[]
     */
    private $excludedPaths;

    /**
     * @var array
     */
    private $includePaths;

    /**
     * @var string|null
     */
    private $adminEmail;
    /**
     * @var string
     */
    private $userRepository;

    /** @var RouteDecorator[] */
    private $routes;


    public function __construct(
        PathResolverInterface $pathResolver,
        RouterInterface       $router,
        array                 $excludedPaths = [],
        array                 $includePaths = [],
        ?string               $adminEmail = null,
        ?string               $userRepository = null
    ) {
        $this->pathResolver = $pathResolver;
        $this->router = $router;
        $this->excludedPaths = $excludedPaths;
        $this->includePaths = $includePaths;
        $this->adminEmail = $adminEmail;
        $this->userRepository = $userRepository;

        $this->initialize();
    }

    private function initialize(): void
    {
        /** @var Route $route */
        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            if (in_array($route->getPath(), $this->excludedPaths)) {
                continue;
            }
            $decoratedRoute = new RouteDecorator($route, $routeName);
            $this->pathResolver->resolve($decoratedRoute);
            $this->routes[] = $decoratedRoute;
        }
    }

    /**
     * @return RouteDecorator[]
     */
    public function getRelevantRoutes(): array
    {
        $resolvedPaths = array_filter($this->routes, function (RouteDecorator $route) {
            return $route->getResolvedPath() !== null;
        });

        return array_merge($resolvedPaths, $this->makeRouteDocoratorsFromIncluded());
    }

    private function makeRouteDocoratorsFromIncluded():array
    {
        return array_map(function (array $row) {
            $route = $this->router->getRouteCollection()->get($row['name']);
            if (!$route) {
                throw new InvalidArgumentException("Route '{$row['name']}' not found in the route collection therefore can not be included.");
            }
            $routeDecorator = new RouteDecorator($route, $row['name']);
            $routeDecorator->setResolvedPath($row['path']);
            return $routeDecorator;
        }, $this->includePaths);
    }

    /**
     * Get routes that could not be resolved and not part of "included" config
     *
     * @return RouteDecorator[]
     */
    public function getUnresolvedRoutes(): array
    {
        return array_filter($this->routes, function (RouteDecorator $route) {
            return $route->getResolvedPath() === null &&
                   !in_array($route->getRouteName(), $this->getIncludedRouteNames());
        });
    }

    private function getIncludedRouteNames():array
    {
        return array_map(function (array $row) {
            return $row['name'];
        }, $this->includePaths);
    }

    /**
     * @return string Yaml list of objects
     */
    public function getListOfUnresolvedPaths(): string
    {
        return join(
            "\n",
            array_map(
                function (RouteDecorator $route) {
                    return "- name: {$route->getRouteName()}\n  path: '{$route->getRoute()->getPath()}'";
                },
                $this->getUnresolvedRoutes()
            )
        );
    }

    /**
     * @return string|null
     */
    public function getAdminEmail(): ?string
    {
        return $this->adminEmail;
    }

    /**
     * @return string
     */
    public function getUserRepository(): string
    {
        return $this->userRepository;
    }
}
