<?php

declare(strict_types=1);

namespace Happyr\ServiceMocking;

use Happyr\ServiceMocking\Proxy\Proxy;
use Happyr\ServiceMocking\Proxy\ProxyDefinition;
use ProxyManager\Proxy\LazyLoadingInterface;

class ServiceMock
{
    private static $definitions = [];

    /**
     * Proxy all method calls from $proxy to $replacement.
     */
    public static function swap($proxy, object $replacement): void
    {
        $definition = self::getDefinition($proxy);
        $definition->swap($replacement);

        // Initialize now so we can use it directly.
        self::initializeProxy($proxy);
    }

    /**
     * Make the next call to $method name execute the $func.
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
     * All folloing calls $methodName will execute $func.
     */
    public static function all($proxy, string $methodName, callable $func): void
    {
        $definition = self::getDefinition($proxy);
        $definition->addMethod($methodName, $func);

        // Initialize now so we can use it directly.
        self::initializeProxy($proxy);
    }

    /**
     * Reset all services.
     */
    public static function resetAll(): void
    {
        foreach (static::$definitions as $definition) {
            $definition->clear();
        }
    }

    /**
     * Reset this service.
     */
    public static function reset($proxy): void
    {
        $definition = self::getDefinition($proxy);
        $definition->clear();
    }

    /**
     * Remove all functions related to $methodName.
     */
    public static function resetMethod($proxy, string $methodName): void
    {
        $definition = self::getDefinition($proxy);
        $definition->removeMethod($methodName);
        $definition->clearMethodsQueue($methodName);
    }

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
        if (!$proxy instanceof LazyLoadingInterface || !method_exists($proxy, 'getWrappedValueHolderValue')) {
            throw new \InvalidArgumentException(\sprintf('Object of class "%s" is not a proxy. Did you mark this service correctly?', get_class($proxy)));
        }

        $key = sha1(get_class($proxy));
        if (!isset(self::$definitions[$key])) {
            self::$definitions[$key] = new ProxyDefinition($proxy->getWrappedValueHolderValue());
        }

        return self::$definitions[$key];
    }
}
