<?php

namespace Mano\AutotestBundle;

use Symfony\Component\Routing\RouteCollection;
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
     * @var array
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

    public function __construct(
        PathResolverInterface $pathResolver,
        RouterInterface $router,
        array $excludedPaths = [],
        ?string $adminEmail = null,
        ?string $userRepository = null
    )
    {
        $this->pathResolver = $pathResolver;
        $this->router = $router;
        $this->excludedPaths = $excludedPaths;
        $this->adminEmail = $adminEmail;
        $this->userRepository = $userRepository;
    }

    /**
     * @return RouteDecorator[]
     */
    public function getRelevantRoutes(): array
    {
        $paths = [];

        $iterator = $this->router->getRouteCollection()->getIterator();

        foreach ($iterator as $route) {
            if (in_array($route->getPath(), $this->excludedPaths)) {
                continue;
            }
            if ($path = $this->pathResolver->resolve($route)) {
                $paths[] = $path;
            }
        }

        return $paths;
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
