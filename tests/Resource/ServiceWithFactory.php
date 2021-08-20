<?php

declare(strict_types=1);

namespace Happyr\ServiceMocking\Tests\Resource;

class ServiceWithFactory
{
    private int $number;
    private int $secretNumber;

    public function __construct(int $number)
    {
        $this->number = $number;
    }

    public static function create(int $number, int $secret)
    {
        $self = new self($number);
        $self->secretNumber = $secret;

        return $self;
    }

    public function getNumber(int $input = 0): int
    {
        return $this->number + $input - $this->secretNumber;
    }

    public function getSecretNumber(): int
    {
        return $this->secretNumber;
    }
}
