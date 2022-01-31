<?php

namespace Mano\AutotestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('autotest');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->arrayNode('exclude')
                    ->prototype('scalar')->end()
                    ->info('Paths that will be excluded from the test.')
                ->end()
                ->arrayNode('include')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('path')->end()
                        ->end()
                    ->end()
                    ->info('Paths that will be included into the test as they could not be resolved automatically. Array of object in format name:{nameOfRoute}, path:{manuallyAddedPath}')
                ->end()
                ->scalarNode('resolver')
                    ->treatNullLike('Mano\AutotestBundle\SimplePathResolver')
                    ->defaultValue('Mano\AutotestBundle\SimplePathResolver')
                    ->info('Custom resolver to be used - must implement PathResolverInterface')
                ->end()
                ->scalarNode('admin_email')
                    ->defaultNull()
                    ->info('Email of admin that can access all the routes - if authorisation needed.')
                ->end()
                ->scalarNode('user_repository')
                    ->treatNullLike('App\Repository\UserRepository')
                    ->defaultValue('App\Repository\UserRepository')
                    ->info('User repository to be used - must have email property defined.')
                ->end()
            ->end();
        return $treeBuilder;
    }
}
