<?php

declare(strict_types=1);

namespace Happyr\ServiceMocking\Tests\Functional;

use Happyr\ServiceMocking\HappyrServiceMockingBundle;
use Happyr\ServiceMocking\ServiceMock;
use Happyr\ServiceMocking\Tests\Resource\ExampleService;
use Happyr\ServiceMocking\Tests\Resource\StatefulService;
use Nyholm\BundleTest\BaseBundleTestCase;
use ProxyManager\Proxy\VirtualProxyInterface;

class BundleInitializationTest extends BaseBundleTestCase
{
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

        $this->assertTrue($container->has(ExampleService::class));
        $service = $container->get(ExampleService::class);

        $this->assertInstanceOf(ExampleService::class, $service);
        $this->assertInstanceOf(VirtualProxyInterface::class, $service);

        $called = false;
        ServiceMock::next($service, 'getNumber', function ($dir) use (&$called) {
            $called = true;
            $this->assertSame(11, $dir);

            return 17;
        });

        $this->assertSame(17, $service->getNumber(11));
        $this->assertTrue($called);

        $mock = $this->getMockBuilder(\stdClass::class)
            ->disableOriginalConstructor()
            ->addMethods(['getNumber'])
            ->getMock();
        $mock->expects($this->once())->method('getNumber')->willReturn(2);
        ServiceMock::swap($service, $mock);

        $this->assertSame(2, $service->getNumber());
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

        $container = $this->getContainer();
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

        $container = $this->getContainer();
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

        $this->assertTrue($container->has(ExampleService::class));
        $service = $container->get(ExampleService::class);

        $this->assertInstanceOf(ExampleService::class, $service);
    }
}
