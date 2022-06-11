<?php

declare(strict_types=1);

namespace Happyr\ServiceMocking\Tests\Resource;

class ServiceWithDestructor
{
    public function __destruct()
    {
    }
}
