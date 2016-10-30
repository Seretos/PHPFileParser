PHPFileParser
=============

This library can be used to parse an PHP file and response all used namespaces
in this file.

WARNING: this library does not use the autoloader. They can't tell, if an class
used in the local namespace, or in the global namespace.

Installation
------------

Add this repository to your composer.json as below:
```php
{
    "require-dev": {
        ...
        "seretos/php-file-parser": "0.1.*"
    }
}
```

Usage
-----

```php
use Symfony\Component\Finder\Finder;
use PHPFileParser\PHPFileParser;

$finder = new Finder();
$finder->files()->in(__DIR__);

foreach($finder as $file){
    $parser = new PHPFileParser($file);
    $parser->parse();               // parse the php file
    $parser->getCalls();            // get an array of used classes (with fully qualified namespace)
    $parser->getNamespaces();       // returns an array with namespaces. every item has an 'use' and an 'alias' key
    $parser->getAnnotationCalls()   // get all classes used in annotation comments
    $parser->getAllUsedNamespaces() // get all used classes (getCalls and getAnnotationCalls) and remove all doubles
    $parser->getNamespace();        // returns null or the current namespace
}
```
