<?php

namespace Mano\AutotestBundle\Test;

use Mano\AutotestBundle\Autotest;
use Mano\AutotestBundle\RouteDecorator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class PhpUnitWebTest extends WebTestCase
{
    /**
     * @var AbstractBrowser
     */
    protected $client;

    /** @var Autotest */
    protected $autotest;

    /* TODO remove __construct as described in deprecations - it can not be replaced by setUpBeforeClass() as dataProvider is called beforehand */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $kernel = self::bootKernel();

        $container = $kernel->getContainer();

        /** @var Autotest $autotest */
        $this->autotest = $container->get('mano_autotest.autotest');
    }

    public function setUp(): void
    {
        static $firstCall = true;
        if ($firstCall === true) {

            echo "Autotest unresolved paths:\n[\n";
            echo $this->autotest->getListOfUnresolvedPaths();
            echo "\n]\n";

            $firstCall = false;
        }

        self::ensureKernelShutdown();
        $this->client = static::createClient();
    }

    /**
     * @dataProvider getPaths
     */
    public function testByAutotest(string $path): void
    {
        if ($this->autotest->getAdminEmail()) {
            $this->logInUser();
        }

        $this->client->request('GET', $path);
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertTrue(
            $statusCode >= 200 && $statusCode < 300,
            "Response code {$statusCode} not within 2** range."
        );
    }


    public function getPaths(): array
    {
        return array_map(function (RouteDecorator $routeDecorator) {
            return [$routeDecorator->getResolvedPath()];
        }, $this->autotest->getRelevantRoutes());
    }

    protected function logInUser(): void
    {
        try {
            $userRepository = $this->client->getContainer()->get($this->autotest->getUserRepository());
        } catch (ServiceNotFoundException $e) {
            throw new \InvalidArgumentException(sprintf("User repository '%s' not found. Define it in the the 
                config under key user_repository.", $this->autotest->getUserRepository()));
        }

        try {
            $testUser = $userRepository->findOneByEmail($this->autotest->getAdminEmail());
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }

        if (!$testUser) {
            throw new \Exception(sprintf('User with email %s not found.', $this->autotest->getAdminEmail()));
        }
        $this->client->loginUser($testUser);
    }
}
