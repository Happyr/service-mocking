<?php

declare(strict_types=1);

namespace Happyr\ServiceMocking\Proxy;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * @internal
 */
class ProxyDefinition
{
    private $originalObject;
    private $methods = [];
    private $methodsQueue = [];
    private $replacement;

    public function __construct(object $originalObject)
    {
        $this->originalObject = $originalObject;
    }

    public function swap(object $replacement): void
    {
        $this->clear();
        $this->replacement = $replacement;
    }

    public function clear(): void
    {
        $this->methods = [];
        $this->methodsQueue = [];
        $this->replacement = null;
    }

    /**
     * Get an object to execute a method on.
     */
    public function getObject(): object
    {
        return $this->replacement ?? $this->originalObject;
    }

    public function getOriginalObject(): object
    {
        return $this->originalObject;
    }

    public function getMethodCallable(string $method): ?callable
    {
        if (isset($this->methodsQueue[$method])) {
            $key = array_key_first($this->methodsQueue[$method]);
            if (null !== $key) {
                $func = $this->methodsQueue[$method][$key];
                unset($this->methodsQueue[$method][$key]);

                return $func;
            }
        }

        if (isset($this->methods[$method])) {
            return $this->methods[$method];
        }

        return null;
    }

    public function addMethod(string $method, callable $func): void
    {
        if ($this->replacement) {
            throw new \LogicException('Cannot add a method after added a replacement');
        }

        $this->methods[$method] = $func;
    }

    public function removeMethod(string $method): void
    {
        unset($this->methods[$method]);
    }

    public function appendMethodsQueue(string $method, callable $func): void
    {
        if ($this->replacement) {
            throw new \LogicException('Cannot add a method after added a replacement');
        }

        $this->methodsQueue[$method][] = $func;
    }

    public function clearMethodsQueue(string $method): void
    {
        unset($this->methodsQueue[$method]);
    }
}
