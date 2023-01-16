## Dependencies Container with PHP8 Attributes

Simple Dependencies Container following PSR-11 standards, using php8 Attributes like Spring.

## Installation

```sh
$ composer require cczzjj/di
```

## Usage

### Default behavior

```php
$container = new DI\Container;

$instance = new stdClass;

$container->set('key', $instance);

$container->has('key'); // true

$object = $container->get('key');

echo $object === $instance . PHP_EOL; // true

class Student {
    public function __construct(private string $name) {}

    public  function getName(): string{
        return $this->name;
    }
}

/** @var Student $student */
$student = $container->make(Student::class, 'Jack');

echo $student->getName(); // Jack
```

### PHP8 Attributes

```php
class Entity1 {

    #[Autowired]
    private Entity2 $entity2;

    public  function getEntity2(): Entity2{
        return $this->entity2;
    }
}

class Entity2 {

    #[Autowired]
    private Entity1 $entity1;

    public  function getEntity1(): Entity1{
        return $this->entity1;
    }
}

$container = new DI\Container;

/** @var Entity1 $entity1 */
$entity1 = $container->get(Entity1::class);

/** @var Entity2 $entity2 */
$entity2 = $container->get(Entity2::class);

echo $entity1->getEntity2() === $entity2; // true
```