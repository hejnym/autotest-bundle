<?php

namespace Mano\AutotestBundle\Tests;

use Mano\AutotestBundle\Autotest;
use Mano\AutotestBundle\Exception\InvalidArgumentException;
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

    public function testOnlyRoutesWithoutWildcardsAndWithGetMethodRelevant()
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

        // only those two bellow
        $this->assertCount(2, $pathNames);

        // default added
        $this->assertContains('/bar/bar', $pathNames);

        // no wildcard
        $this->assertContains('/foo', $pathNames);
    }

    public function testExludedRoutesNotPartOfRelevant()
    {
        // exclude /bar/{bar} and /foo
        $kernel = new TestKernel([
            'exclude' => [
                '/fo{2}',
                '/bar/.*'
            ]
        ]);
        $kernel->boot();
        $container = $kernel->getContainer();
        /** @var Autotest $autotestService */
        $autotestService = $container->get('mano_autotest.autotest');
        $this->assertCount(0, $autotestService->getRelevantRoutes());
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
        $this->assertCount(5, $paths);
        $this->assertEquals('foo', $paths[0]->getResolvedPath());
    }

    public function testIncludedMustBeValidRouteName()
    {
        $kernel = new TestKernel([
            'include' => [
                [
                    'name' => 'nonexisting name',
                    'path' => 'whatever'
                ]
            ]
        ]);

        $kernel->boot();
        $container = $kernel->getContainer();
        /** @var Autotest $autotestService */
        $autotestService = $container->get('mano_autotest.autotest');

        $this->expectException(InvalidArgumentException::class);
        $autotestService->getRelevantRoutes();
    }

    public function testIncludedArePartOfRelevantRoutes()
    {
        $kernel = new TestKernel([
            'include' => [
                [
                    'name' => 'fook',
                    'path' => 'whatever'
                ]
            ]
        ]);

        $kernel->boot();
        $container = $kernel->getContainer();
        /** @var Autotest $autotestService */
        $autotestService = $container->get('mano_autotest.autotest');

        $paths = $autotestService->getRelevantRoutes();

        $pathNames = array_map(function (RouteDecorator $route) {
            return $route->getResolvedPath();
        }, $paths);

        // only foo and foolo
        $this->assertCount(3, $pathNames);
        $this->assertContains('whatever', $pathNames);
    }
}
