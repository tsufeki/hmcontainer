HMContainer
===========

HMContainer is a hierarchical dependency injection container for PHP, inspired
by Angular's Injector.

Installation
------------

With [Composer](https://getcomposer.org/):
```
$ composer require tsufeki/hmcontainer
```

Usage
-----

Create a container:

```php
use Tsufeki\HmContainer\Container;

$c = new Container();
```

Add a value (constant parameter):

```php
$c->setValue("key", 42);
```

Retrieve it (HMContainer implements [PSR-11](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-11-container.md)):

```php
$c->has("key"); // true
$c->get("key"); // 42
$c->get("non-existent-key"); // throws NotFoundException
$c->getOrDefault("non-existent-key", 5); // 5
```

Container is frozen during first `get()` or `freeze()` call and no new items can
be added afterwards.

### Multi-valued keys

Add multiple items to be retrieved as an array:

```php
$c->setValue("primes", 2, ['multi' => true]);
$c->setValue("primes", 3, ['multi' => true]);
$c->setValue("primes", 5, ['multi' => true]);
$c->isMulti("primes"); // true
$c->get("primes"); // [2, 3, 5]
```

### Class instantiation and autowiring

Add a class which will be instantiated once, during first `get()`:

```php
$c->setClass("aobject", AClass::class, [], ["dep1", "dep2"]);
$c->get("aobject"); // returns new AClass($c->get("dep1"), $c->get("dep2"))
$c->get("aobject"); // returns the same instance as above
```

Dependencies can be automatically deduced (autowired) when using class names as keys:

```php
class BClass { }

class CClass {
  public function __construct(BClass $b) { }
}

$c->setClass(BClass::class);
$c->setClass(CClass::class);
$c->get(CClass::class); // correctly contructed CClass object
```

Autowiring key is guessed from parameter type hint, `@param` tag type or special `@Inject` tag:

```php
class DClass {
  /**
   * @param CClass $c
   * @param $d @Inject("dkey")
   */
  public function __construct(BClass $b, $c, $d) { }
}
```

Multi items are supported as well:

```php
class Aggregator {
  /**
   * @param SomeInterface[] $impls
   */
  public function __construct(array $impls) { }
}

$c->setClass(SomeInterface::class, ConcreteImplementation1::class, ['multi' => true]);
$c->setClass(SomeInterface::class, ConcreteImplementation2::class, ['multi' => true]);
$c->setClass(Aggregator::class);
$c->get(Aggregator::class);
```

Mark parameter with `@Optional` to have `null` injected when dependency can't
be found:

```php
class Maybe {
  /**
   * @param $dep @Optional
   */
  public function __construct(Dep $dep = null) { }
}
```

Mix manual dependencies and autowiring by putting some `null`s in dependency
array:

```php
$c->setClass(DClass::class, null, [], [null, "dep2"]);
```

### Aliases

Add an alias to other key:

```php
$c->setAlias("alias", "target");
$c->get("alias"); // same as $c->get("target")
```

### Lazy items

Add a lazy item with `'lazy'` option, it will return parameterless callable:

```php
$c->setValue('lazy', 42, ['lazy' => true]);
$c->get('lazy'); // a callable $f such that $f() === 42
```

### Container hierarchy

Create a child container:

```php
$child = new Container($c);
```

For single valued keys, lookups check the parent container when item is not
found in child. For multi keys, values from parent and child are merged.

### Custom factories

You can add your custom instantiators by implementing
[FactoryInterface](src/Tsufeki/HmContainer/FactoryInterface.php) and using
`set()` method:

```php
$myFactory = new MyFactory();
$c->set("mykey", $myFactory);
```

### Serialization

Container can be serialized and unserialized for caching with standard PHP
`serialize()` and `unserialize()`, but only if you use serializable factories.

License
-------
MIT - see [LICENCE](LICENSE).

