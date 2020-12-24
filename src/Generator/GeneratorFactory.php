<?php

declare(strict_types=1);

namespace Happyr\ServiceMocking\Generator;

use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

/**
 * Factory responsible of producing virtual proxy instances.
 */
class GeneratorFactory extends LazyLoadingValueHolderFactory
{
    private $generator;

    public function __construct(?Configuration $configuration = null)
    {
        parent::__construct($configuration);

        $this->generator = new LazyLoadingValueHolderGenerator();
    }

    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator;
    }
}
