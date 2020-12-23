<?php

declare(strict_types=1);

namespace Happyr\ServiceMocking\Tests\Functional;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Happyr\ServiceMocking\HappyrServiceMocking;
use Happyr\ServiceMocking\HappyrServiceMockingBundle;
use Happyr\ServiceMocking\Service\Calculator;
use Happyr\ServiceMocking\Service\DistributionManager;
use Happyr\ServiceMocking\Service\StatisticsHelper;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use ProxyManager\Proxy\VirtualProxyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpKernel\Log\Logger;

class BundleInitializationTest extends BaseBundleTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->addCompilerPass(new PublicServicePass('|router|'));
    }

    protected function getBundleClass()
    {
        return HappyrServiceMockingBundle::class;
    }

    public function testInitBundle()
    {
        $kernel = $this->createKernel();
        $kernel->addConfigFile(__DIR__.'/config.yml');

        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();

        $this->assertTrue($container->has('router'));
        $service = $container->get('router');

        $this->assertInstanceOf(Router::class, $service);
        $this->assertInstanceOf(VirtualProxyInterface::class, $service);
    }


    public function testInitEmptyBundle()
    {
        $kernel = $this->createKernel();
        $kernel->addConfigFile(__DIR__.'/empty.yml');

        // Boot the kernel.
        $this->bootKernel();

        // Get the container
        $container = $this->getContainer();

        $this->assertTrue($container->has('router'));
        $service = $container->get('router');

        $this->assertInstanceOf(Router::class, $service);
    }
}
