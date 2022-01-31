<?php

namespace Mano\AutotestBundle;

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
        ?string               $adminEmail = null,
        ?string               $userRepository = null
    ) {
        $this->pathResolver = $pathResolver;
        $this->router = $router;
        $this->excludedPaths = $excludedPaths;
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
        $resolvedPaths =  array_filter($this->routes, function (RouteDecorator $route) {
            return $route->getResolvedPath() !== null;
        });

        return $resolvedPaths;

    }

    /**
     * @return RouteDecorator[]
     */
    public function getUnresolvedRoutes(): array
    {
        return array_filter($this->routes, function (RouteDecorator $route) {
            return $route->getResolvedPath() === null;
        });
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
