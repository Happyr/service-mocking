<?php

namespace Happyr\ServiceMocking\Tests\Unit\Proxy;

use Happyr\ServiceMocking\Proxy\ProxyDefinition;
use PHPUnit\Framework\TestCase;

class ProxyDefinitionTest extends TestCase
{
    public function testSwap()
    {
        $a = new \stdClass();
        $proxy = new ProxyDefinition($a);
        $this->assertSame($a, $proxy->getObject());

        $b = new \stdClass();
        $proxy->swap($b);
        $this->assertSame($b, $proxy->getObject());

        $proxy->clear();
        $this->assertSame($a, $proxy->getObject());
    }
}
