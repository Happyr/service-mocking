<?php

namespace Happyr\ServiceMocking\DependencyInjection\CompilerPass;

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;
use ProxyManager\Proxy\LazyLoadingInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProxyServiceWithMockPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $serviceIds = $container->getParameter('happyr_service_mock.services');

        foreach ($container->findTaggedServiceIds('happyr_service_mock') as $id => $tags) {
            $serviceIds[] = $id;
        }

        $proxiesDirectory = $container->getParameter('kernel.cache_dir').'/happyr_service_mock';
        @mkdir($proxiesDirectory);

        $config = new Configuration();
        $config->setGeneratorStrategy(new FileWriterGeneratorStrategy(new FileLocator($proxiesDirectory)));
        $config->setProxiesTargetDir($proxiesDirectory);
        \spl_autoload_register($config->getProxyAutoloader());
        $factory = new LazyLoadingValueHolderFactory($config);

        foreach ($serviceIds as $serviceId) {
            if ($container->hasDefinition($serviceId)) {
                $definition = $container->getDefinition($serviceId);
            } elseif ($container->hasAlias($serviceId)) {
                $definition = $container->getDefinition($container->getAlias($serviceId));
            } else {
                throw new \LogicException(sprintf('[HappyrServiceMocking] Service or alias with id "%s" does not exist.', $serviceId));
            }

            $initializer = function (&$wrappedObject, LazyLoadingInterface $proxy, $method, array $parameters, &$initializer) {
                $initializer = null; // disable initialization
                $foobar = 'foobar';

                return true; // make sure this callable is always called
            };

            $proxy = $factory->createProxy($definition->getClass(), $initializer);
            $definition->setClass(get_class($proxy));
        }
    }
}
