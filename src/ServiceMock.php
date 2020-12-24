<?php

declare(strict_types=1);


namespace Happyr\ServiceMocking;

use Happyr\ServiceMocking\Proxy\ProxyDefinition;
use Happyr\ServiceMocking\Proxy\Proxy;
use ProxyManager\Proxy\LazyLoadingInterface;

class ServiceMock
{
    private static $definitions = [];

    /**
     * Make the next call to $method name execute the $func
     */
    public static function next($proxy, string $methodName, callable ...$func): void
    {
        $definition = self::initializeProxy($proxy);
        foreach ($func as $f) {
            $definition->addMethod($methodName, $f);
        }
    }

    /**
     * All folloing calls $methodName will execute $func
     */
    public static function all($proxy, string $methodName, callable $func): void
    {
        $definition = self::initializeProxy($proxy);
        $definition->addMethod($methodName, $func);
    }

    /**
     * Remove all functions related to $methodName.
     */
    public static function clear($proxy, string $methodName): void
    {
        $definition = self::initializeProxy($proxy);
        $definition->removeMethod($methodName);
        $definition->clearMethodsQueue($methodName);
    }

    /**
     * @param LazyLoadingInterface $proxy
     */
    public static function initializeProxy($proxy)
    {
        if (!$proxy instanceof LazyLoadingInterface) {
            throw new \InvalidArgumentException(\sprintf('Object of class "%s" is not a proxy. Did you mark this service correctly?', get_class($proxy)));
        }

        $key = spl_object_hash($proxy);
        if (isset(self::$definitions[$key])) {
            $definition = self::$definitions[$key];
        } else {
            $definition = self::$definitions[$key] = new ProxyDefinition($proxy->getWrappedValueHolderValue());
        }

        if ($proxy->getProxyInitializer() === null) {
            $initializer = function (&$wrappedObject, LazyLoadingInterface $proxy, $calledMethod, array $parameters, &$nextInitializer) use ($definition) {
                $nextInitializer = null;
                $wrappedObject = new Proxy($definition);

                return true;
            };
            $proxy->setProxyInitializer($initializer);
        }

        return $definition;
    }
}
