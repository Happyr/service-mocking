<?php

declare(strict_types=1);

namespace Happyr\ServiceMocking\Tests\Functional;

use Happyr\ServiceMocking\HappyrServiceMockingBundle;
use Happyr\ServiceMocking\ServiceMock;
use Happyr\ServiceMocking\Tests\Resource\ExampleService;
use Happyr\ServiceMocking\Tests\Resource\ServiceWithFactory;
use Happyr\ServiceMocking\Tests\Resource\StatefulService;
use Nyholm\BundleTest\TestKernel;
use ProxyManager\Proxy\VirtualProxyInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class BundleInitializationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        if (null !== static::$kernel) {
            return static::$kernel;
        }
        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->setClearCacheAfterShutdown(false);
        $kernel->addTestBundle(HappyrServiceMockingBundle::class);
        $configFile = $options['config_file'] ?? 'config.yml';
        $kernel->addTestConfig(__DIR__.'/'.$configFile);
        unset($options['config_file']);

        $kernel->handleOptions($options);

        return $kernel;
    }

    public function testInitBundle()
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

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

        $serviceWithFactory = $container->get(ServiceWithFactory::class);
        $this->assertSame(3, $serviceWithFactory->getSecretNumber());

        $called = false;
        ServiceMock::next($serviceWithFactory, 'getNumber', function ($dir) use (&$called) {
            $called = true;
            $this->assertSame(11, $dir);

            return 17;
        });

        $this->assertSame(17, $serviceWithFactory->getNumber(11));
        $this->assertTrue($called);
        $this->assertSame(14, $serviceWithFactory->getNumber(11));
    }

    public function testRebootBundle()
    {
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

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
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();

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
        $kernel = self::bootKernel(['config_file' => 'empty.yml']);
        $container = $kernel->getContainer();

        $this->assertTrue($container->has(ExampleService::class));
        $service = $container->get(ExampleService::class);

        $this->assertInstanceOf(ExampleService::class, $service);
    }
}
