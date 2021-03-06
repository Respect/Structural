Respect/Structural
==================

[![Latest Stable Version](https://poser.pugx.org/respect/structural/v/stable)](https://packagist.org/packages/respect/structural)
[![Total Downloads](https://poser.pugx.org/respect/structural/downloads)](https://packagist.org/packages/respect/structural)
[![Latest Unstable Version](https://poser.pugx.org/respect/structural/v/unstable)](https://packagist.org/packages/respect/structural)
[![License](https://poser.pugx.org/respect/structural/license)](https://packagist.org/packages/respect/structural)

[![Build Status](https://travis-ci.org/Respect/Structural.svg?branch=master)](https://travis-ci.org/Respect/Structural)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Respect/Structural/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Respect/Structural/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Respect/Structural/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Respect/Structural/?branch=master)
[![StyleCI](https://styleci.io/repos/2753337/shield)](https://styleci.io/repos/2753337)

### The Near-zero Part

```php
// bootstrap.php
require_once __DIR__ . '/vendor/autoload.php';

use Respect\Structural\Mapper;
use Respect\Structural\Driver\MongoDb\Style as MongoDbStyle;
use Respect\Structural\Driver\MongoDb\Driver as MongoDbDriver;

$driver = MongoDbDriver::factory('respect');

$mapper = new Mapper($driver);
$mapper->setStyle(new MongoDbStyle());
```

### Persisting
```php
$author = new \stdClass();
$author->firstName = 'Antonio';
$mapper->authors->persist($author);
$mapper->flush();

echo "'{$author->firstName}' was created with id({$author->_id})".PHP_EOL;
```

### Updating
```php
$author->lastName = 'Spinelli';
$mapper->authors->persist($author);
$mapper->flush();

echo "last name was updated to '{$author->lastName}' from id({$author->_id})".PHP_EOL;
```

### Fetching
```php
$authors = $mapper->authors->fetchAll();

echo "Fetching all authors:" . PHP_EOL;
foreach ($authors as $index => $author) {
    echo "{$index} {$author->firstName} {$author->lastName}" . PHP_EOL;
}
```

### Condition
```php
// find author by ID
$foundAuthor = $mapper->authors[(string)$author->_id]->fetch();
echo "find by id('{$author->_id}') {$foundAuthor->firstName} {$foundAuthor->lastName}".PHP_EOL;
```

### Removing
```php
$mapper->authors->remove($author);
$mapper->flush();

$author = $mapper->authors(['lastName' => 'Spinelli'])->fetch();
echo ($author ? "'Spinelli' was found" : "'Spinelli' removed.");
```
