<?php

declare(strict_types=1);

namespace Happyr\ServiceMocking\Tests\Functional;

use Happyr\ServiceMocking\HappyrServiceMockingBundle;
use Happyr\ServiceMocking\ServiceMock;
use Happyr\ServiceMocking\Tests\Resource\StatefulService;
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

        $called = false;
        ServiceMock::next($service, 'warmUp', function ($dir) use (&$called) {
            $called = true;
            $this->assertSame('foo', $dir);
        });

        $service->warmUp('foo');
        $this->assertTrue($called);

        $mock = $this->getMockBuilder(\stdClass::class)
            ->disableOriginalConstructor()
            ->addMethods(['warmUp'])
            ->getMock();
        $mock->expects($this->once())->method('warmUp')->willReturn(true);
        ServiceMock::swap($service, $mock);

        $this->assertTrue($service->warmUp('foo'));
    }

    public function testRebootBundle()
    {
        $kernel = $this->createKernel();
        $kernel->addConfigFile(__DIR__.'/config.yml');

        $this->bootKernel();
        $container = $this->getContainer();

        $this->assertTrue($container->has(StatefulService::class));
        $service = $container->get(StatefulService::class);
        $service->setData('foobar');
        $this->assertNotNull($service->getData());
        ServiceMock::next($service, 'getData', function () {
            return 'secret';
        });

        $this->bootKernel();

        $service = $container->get(StatefulService::class);
        $this->assertSame('secret', $service->getData());
        $this->assertNull($service->getData());
    }

    public function testReloadRealObjectOnRebootBundle()
    {
        $kernel = $this->createKernel();
        $kernel->addConfigFile(__DIR__.'/config.yml');

        $this->bootKernel();
        $container = $this->getContainer();

        $this->assertTrue($container->has(StatefulService::class));
        $service = $container->get(StatefulService::class);
        $service->setData('foobar');
        $this->assertNotNull($service->getData());
        $this->bootKernel();

        $service = $container->get(StatefulService::class);
        $this->assertNull($service->getData(), 'The real service object is not reloaded on kernel reboot.');
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
