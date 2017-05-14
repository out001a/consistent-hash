# consistent hash

## Usage

```php
$chash = new ConsistentHash('test');

$chash->addNode('127.0.0.1:80');
$chash->addNode('127.0.0.1:81');
$chash->addNode('127.0.0.1:82');
$chash->addNode('127.0.0.1:83');

var_dump('abc -> '.$chash->lookup('abc'));
var_dump('def -> '.$chash->lookup('def'));
var_dump('ghi -> '.$chash->lookup('ghi'));
```