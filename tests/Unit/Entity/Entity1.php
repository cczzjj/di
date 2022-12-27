<?php

declare(strict_types=1);

namespace Tests\Unit\Entity;

class Entity1
{
    public function __construct(private ?string $name = '')
    {
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}