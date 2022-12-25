<?php

declare(strict_types=1);

namespace Tests\Unit\Entity;

use DI\Attribute\Autowired;

class Entity3
{
    #[Autowired]
    protected $property2;

    public function getProperty2()
    {
        return $this->property2;
    }
}