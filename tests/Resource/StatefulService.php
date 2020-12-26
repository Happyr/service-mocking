<?php

declare(strict_types=1);

namespace Happyr\ServiceMocking\Tests\Resource;

class StatefulService
{
    private $data;

    public function getData()
    {
        return $this->data;
    }

    public function setData($data): void
    {
        $this->data = $data;
    }
}
