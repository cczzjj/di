<?php

declare(strict_types=1);

namespace Tests\Unit\Entity;

use DI\Attribute\Autowired;

class Entity5
{
    #[Autowired]
    private Entity4 $property4;

    public function getProperty4(): Entity4
    {
        return $this->property4;
    }
}