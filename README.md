# consistent hash

## Usage

```bash
$ composer require out001a/consistent-hash
```

```php
require 'vendor/autoload.php';

use Out001a\ConsistentHash;

$chash = new ConsistentHash('test');

// 增加节点
$chash->addNode('127.0.0.1:80');
$chash->addNode('127.0.0.1:81');
$chash->addNode('127.0.0.1:82');

// 查找字符串哈希到的节点
var_dump('abc -> '.$chash->lookup('abc'));
var_dump('def -> '.$chash->lookup('def'));
var_dump('ghi -> '.$chash->lookup('ghi'));

echo "\n========\n\n";

// 删除某个节点
$chash->removeNode('127.0.0.1:82');

// 再次查找节点
var_dump('abc -> '.$chash->lookup('abc'));
var_dump('def -> '.$chash->lookup('def'));
var_dump('ghi -> '.$chash->lookup('ghi'));
```
