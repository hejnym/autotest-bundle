<?php

namespace Mano\AutotestBundle\Tests;

use Mano\AutotestBundle\AutotestBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    /**
     * @var array
     */
    private $autotestConfig;

    public function __construct(array $autotestConfig = [])
    {
        parent::__construct('test', true);
        $this->autotestConfig = $autotestConfig;
    }

    public function registerBundles()
    {
        return [
          new FrameworkBundle(),
          new AutotestBundle()
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {

            // make router available
            $container->loadFromExtension('framework', [
                'router' => [
                    'resource' => __DIR__.'/routes.xml',
                ],
            ]);
            $container->register('test_path_resolver', TestPathResolver::class);
            $container->loadFromExtension('autotest', $this->autotestConfig);
        });
    }

    /*
     * Make sure unique cached kernel for each config
     */
    public function getCacheDir()
    {
        return __DIR__.'/../var/cache/kernel/'.md5(serialize($this->autotestConfig));
    }
}
