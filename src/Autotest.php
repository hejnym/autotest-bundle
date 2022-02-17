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
    private $excludedPatterns;

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
    private $routes = [];


    public function __construct(
        PathResolverInterface $pathResolver,
        RouterInterface       $router,
        array                 $excludedPatterns = [],
        array                 $includePaths = [],
        ?string               $adminEmail = null,
        ?string               $userRepository = null
    ) {
        $this->pathResolver = $pathResolver;
        $this->router = $router;
        $this->excludedPatterns = $excludedPatterns;
        $this->includePaths = $includePaths;
        $this->adminEmail = $adminEmail;
        $this->userRepository = $userRepository;

        $this->initialize();
    }

    private function initialize(): void
    {
        /** @var Route $route */
        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            if ($this->isToBeExcluded($route)) {
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

        return array_merge($resolvedPaths, $this->makeRouteDecoratorsFromIncluded());
    }

    private function makeRouteDecoratorsFromIncluded():array
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

    private function isToBeExcluded(Route $route):bool
    {
        foreach ($this->excludedPatterns as $excludedPattern) {
            $regex = '~^'.$excludedPattern.'$~i';
            if (preg_match($regex, $route->getPath())) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string|null Yaml list of objects or null then all resolved.
     */
    public function getListOfUnresolvedPaths(): ?string
    {
        $unresolvedRoutes = $this->getUnresolvedRoutes();

        if (!$unresolvedRoutes) {
            return null;
        }

        return join(
            "\n",
            array_map(
                function (RouteDecorator $route) {

                    $comment = $route->getResolverComment()? "# ".$route->getResolverComment():'';

                    return sprintf(
                        "%s\n- name: %s\n  path: '%s'",
                        $comment,
                        $route->getRouteName(),
                        $route->getRoute()->getPath()
                    );
                },
                $unresolvedRoutes
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
