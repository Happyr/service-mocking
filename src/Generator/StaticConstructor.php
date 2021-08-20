<?php

declare(strict_types=1);

namespace Happyr\ServiceMocking\Generator;

use Laminas\Code\Generator\Exception\InvalidArgumentException;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use ProxyManager\Generator\MethodGenerator;

/**
 * The `__construct_with_factory` implementation for lazy loading proxies. This
 * is used for services created with service factories.
 *
 * @interal
 */
class StaticConstructor extends MethodGenerator
{
    /**
     * @throws InvalidArgumentException
     */
    public static function generateMethod(PropertyGenerator $valueHolder): self
    {
        $constructor = new self('__construct_with_factory');
        $constructor->setStatic(true);
        $constructor->setParameter(new ParameterGenerator('factory', 'callable'));
        $parameter = new ParameterGenerator('arguments');
        $parameter->setVariadic(true);
        $constructor->setParameter($parameter);

        $constructor->setBody(
            'static $reflection;'."\n\n"
            .'$reflection = $reflection ?? new \ReflectionClass(self::class);'."\n"
            .'$model = $reflection->newInstanceWithoutConstructor();'."\n"
            .'$model->'.$valueHolder->getName().' = \Closure::fromCallable($factory)->__invoke(...$arguments);'."\n"
            .'\Happyr\ServiceMocking\ServiceMock::initializeProxy($model);'."\n\n"
            .'return $model;'
        );

        return $constructor;
    }
}
