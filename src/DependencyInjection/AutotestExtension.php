<?php

namespace Mano\AutotestBundle\DependencyInjection;

use Mano\AutotestBundle\SimplePathResolver;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AutotestExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('mano_autotest.autotest');

        if ($config['resolver'] !== SimplePathResolver::class) {
            $container->setAlias('mano_autotest.path_resolver_interface', $config['resolver']);
        }
        $definition->setArgument(2, $config['exclude']);
        $definition->setArgument(3, $config['include']);
        $definition->setArgument(4, $config['admin_email']);
        $definition->setArgument(5, $config['user_repository']);
    }
}
