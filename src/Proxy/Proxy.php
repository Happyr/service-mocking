<?php

declare(strict_types=1);


namespace Happyr\ServiceMocking\Proxy;

/**
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * @internal
 */
class Proxy
{
    private $definition;

    public function __construct(ProxyDefinition $definition)
    {
        $this->definition = $definition;
    }

    public function __call($method, $args)
    {
        $func = $this->definition->getMethodCallable($method);
        if (null === $func) {
            return $this->definition->getOriginalObject()->{$method}(...$args);
        } else {
            return call_user_func_array($func, $args);
        }
    }
}
