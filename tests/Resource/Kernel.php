<?php

namespace Happyr\ServiceMocking\Tests\Resource;

use Nyholm\BundleTest\TestKernel;

class Kernel extends TestKernel
{
    public function shutdown($clearCache = true): void
    {
        parent::shutdown(false);
    }
}
