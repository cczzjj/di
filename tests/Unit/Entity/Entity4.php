<?php

declare(strict_types=1);

namespace Tests\Unit\Entity;

use DI\Attribute\Autowired;

class Entity4
{
    #[Autowired]
    private Entity5 $property5;

    #[Autowired]
    private Entity6 $property6;

    public function getProperty5(): Entity5
    {
        return $this->property5;
    }

    public function getProperty6(): Entity6
    {
        return $this->property6;
    }
}