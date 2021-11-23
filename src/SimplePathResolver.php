<?php

namespace Mano\AutotestBundle;

use Symfony\Component\Routing\Route;

/**
 * Resolver that passes only GET method without wildcards (except defaults)
 */
class SimplePathResolver implements PathResolverInterface
{
    public function resolve(Route $route): ?RouteDecorator
    {
        if (!$this->hasGetMethod($route)) {
            return null;
        }

        $pathWithDefaults = $this->fillDefaults($route);
        if ($this->hasWildcard($pathWithDefaults)) {
            return null;
        } else {
            $routeDecorator = new RouteDecorator($route);
            $routeDecorator->setResolvedPath($pathWithDefaults);
            return $routeDecorator;
        }
    }

    private function fillDefaults(Route $route): string
    {
        $replaced = $route->getPath();
        foreach ($route->getDefaults() as $name => $value) {
            if ($name !== '_controller' && strpos($replaced, '{'.$name.'}')) {
                $replaced = str_replace('{'.$name.'}', $value, $replaced);
            }
        }
        return $replaced;
    }

    private function hasWildcard(string $path): bool
    {
        if (strpos($path, '{')) {
            return true;
        }
        return false;
    }

    private function hasGetMethod(Route $route): bool
    {
        if (!$route->getMethods()) {
            return true;
        }

        return in_array('GET', $route->getMethods());
    }
}
