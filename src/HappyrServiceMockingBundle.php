<?php

declare(strict_types=1);

namespace Happyr\ServiceMocking;

use Happyr\ServiceMocking\DependencyInjection\CompilerPass\ProxyServiceWithMockPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class HappyrServiceMockingBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ProxyServiceWithMockPass());
    }
}
