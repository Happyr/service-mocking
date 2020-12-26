<?php

declare(strict_types=1);

namespace Happyr\ServiceMocking\Tests\Resource;

class ExampleService
{
    public function getNumber(int $input = 0): int
    {
        return 4711 + $input;
    }
}
