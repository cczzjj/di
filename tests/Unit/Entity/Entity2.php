<?php

declare(strict_types=1);

namespace Tests\Unit\Entity;

use DI\Attribute\Autowired;

class Entity2
{
    #[Autowired]
    protected Entity1 $property1;

    public function getProperty1(): Entity1
    {
        return $this->property1;
    }
}