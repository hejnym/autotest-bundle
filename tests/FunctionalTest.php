<?php

namespace Mano\AutotestBundle\Tests;

use Mano\AutotestBundle\Autotest;
use Mano\AutotestBundle\RouteDecorator;
use PHPUnit\Framework\TestCase;

class FunctionalTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new TestKernel();
        $kernel->boot();
        $container = $kernel->getContainer();
        $autotestService = $container->get('mano_autotest.autotest');
        $this->assertInstanceOf(Autotest::class, $autotestService);
    }

    public function testOnlyRelevantRoutesOutputted()
    {
        $kernel = new TestKernel();
        $kernel->boot();
        $container = $kernel->getContainer();
        /** @var Autotest $autotestService */
        $autotestService = $container->get('mano_autotest.autotest');
        $paths = $autotestService->getRelevantRoutes();

        $pathNames = array_map(function (RouteDecorator $route) {
            return $route->getResolvedPath();
        }, $paths);

        $this->assertCount(2, $pathNames);

        // default added
        $this->assertContains('/bar/bar', $pathNames);
        // no wildcard
        $this->assertContains('/foo', $pathNames);
    }

    public function testPathResolverCanBeAltered()
    {
        $kernel = new TestKernel([
            'resolver' => 'test_path_resolver'
        ]);

        $kernel->boot();
        $container = $kernel->getContainer();
        /** @var Autotest $autotestService */
        $autotestService = $container->get('mano_autotest.autotest');
        $paths = $autotestService->getRelevantRoutes();

        // all routes taken
        $this->assertCount(4, $paths);
        $this->assertEquals('foo', $paths[0]->getResolvedPath());
    }
}
