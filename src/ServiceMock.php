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
        $definition = self::getDefinition($proxy);
        foreach ($func as $f) {
            $definition->appendMethodsQueue($methodName, $f);
        }

        // Initialize now so we can use it directly.
        self::initializeProxy($proxy);
    }

    /**
     * All folloing calls $methodName will execute $func
     */
    public static function all($proxy, string $methodName, callable $func): void
    {
        $definition = self::getDefinition($proxy);
        $definition->addMethod($methodName, $func);

        // Initialize now so we can use it directly.
        self::initializeProxy($proxy);
    }

    /**
     * Remove all functions related to $methodName.
     */
    public static function clear($proxy, string $methodName): void
    {
        $definition = self::getDefinition($proxy);
        $definition->removeMethod($methodName);
        $definition->clearMethodsQueue($methodName);
    }

    /**
     * @param LazyLoadingInterface $proxy
     */
    public static function initializeProxy(LazyLoadingInterface $proxy): void
    {
        $initializer = function (&$wrappedObject, LazyLoadingInterface $proxy, $calledMethod, array $parameters, &$nextInitializer) {
            $nextInitializer = null;
            $wrappedObject = new Proxy(self::getDefinition($proxy));

            return true;
        };

        $proxy->setProxyInitializer($initializer);
    }

    /**
     * @param LazyLoadingInterface $proxy
     */
    private static function getDefinition($proxy): ProxyDefinition
    {
        if (!$proxy instanceof LazyLoadingInterface) {
            throw new \InvalidArgumentException(\sprintf('Object of class "%s" is not a proxy. Did you mark this service correctly?', get_class($proxy)));
        }

        $key = sha1(get_class($proxy));
        if (!isset(self::$definitions[$key])) {
            self::$definitions[$key] = new ProxyDefinition($proxy->getWrappedValueHolderValue());
        }

        return self::$definitions[$key];
    }
}
