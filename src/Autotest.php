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

    /**
     * @var Route[]
     */
    private $unresolved = [];

    /**
     * @var RouteDecorator[]
     */
    private $resolved = [];


    public function __construct(
        PathResolverInterface $pathResolver,
        RouterInterface $router,
        array $excludedPaths = [],
        ?string $adminEmail = null,
        ?string $userRepository = null
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
        $iterator = $this->router->getRouteCollection()->getIterator();

        foreach ($iterator as $route) {
            if (in_array($route->getPath(), $this->excludedPaths)) {
                continue;
            }

            $path = $this->pathResolver->resolve($route);
            if ($path) {
                $this->resolved[] = $path;
            } else {
                $this->unresolved[] = $route;
            }
        }
    }

    /**
     * @return RouteDecorator[]
     */
    public function getRelevantRoutes(): array
    {
        return $this->resolved;
    }

    /**
     * @return Route[]
     */
    public function getUnresolvedRoutes(): array
    {
        return $this->unresolved;
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
