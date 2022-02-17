<?php

namespace Mano\AutotestBundle;

use Symfony\Component\Routing\Route;

/**
 * Resolver that passes only GET method without wildcards (except defaults)
 */
class SimplePathResolver implements PathResolverInterface
{
    public function resolve(RouteDecorator $route): RouteDecorator
    {
        if (!$this->hasGetMethod($route)) {
            $route->setResolverComment('Is not GET method.');
            return $route;
        }

        $pathWithDefaults = $this->fillDefaults($route);

        if ($this->hasWildcard($pathWithDefaults)) {
            $route->setResolverComment('Contains wildcard.');
        } else {
            $route->setResolvedPath($pathWithDefaults);
        }

        return $route;
    }

    private function fillDefaults(RouteDecorator $route): string
    {
        $replaced = $route->getRoute()->getPath();
        foreach ($route->getRoute()->getDefaults() as $name => $value) {
            if ($value !== null && $name !== '_controller' && strpos($replaced, '{' . $name . '}')) {
                $replaced = str_replace('{' . $name . '}', $value, $replaced);
            }
        }
        return $replaced;
    }

    private function hasWildcard(string $path): bool
    {
        return strpos($path, '{') !== false;
    }

    private function hasGetMethod(RouteDecorator $route): bool
    {
        if (!$route->getRoute()->getMethods()) {
            return true;
        }

        return in_array('GET', $route->getRoute()->getMethods());
    }
}
