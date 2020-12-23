<?php

declare(strict_types=1);

namespace Happyr\ServiceMocking\Tests\Functional;

use Happyr\ServiceMocking\HappyrServiceMockingBundle;
use Nyholm\BundleTest\BaseBundleTestCase;
use Nyholm\BundleTest\CompilerPass\PublicServicePass;
use ProxyManager\Proxy\VirtualProxyInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

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
