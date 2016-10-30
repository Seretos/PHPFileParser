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
        "seretos/php-file-parser": "0.2.x-dev"
    }
}
```

Usage
-----

```php
use Symfony\Component\Finder\Finder;
use PHPFileParser\PHPFileParser;
use PHPFileParser\Parser\NewParser;
use PHPFileParser\Parser\StaticParser;
use PHPFileParser\Parser\ExtendsParser;
use PHPFileParser\Parser\ImplementsParser;
use PHPFileParser\Parser\CatchParser;
use PHPFileParser\Parser\ArgumentParser;
use PHPFileParser\Parser\AnnotationParser;

$finder = new Finder();
$finder->files()->in(__DIR__);

foreach($finder as $file){
    $parser = new PHPFileParser($file);
    
    $parser->addParser(new NewParser());        // parse new operations like $var = new MyClass();
    $parser->addParser(new StaticParser());     // parse static operations like MyClass::class;
    $parser->addParser(new ExtendsParser());    // parse extended base classes
    $parser->addParser(new ImplementsParser()); // parse implemented class interfaces
    $parser->addParser(new CatchParser());      // parse the catched exception classes
    $parser->addParser(new ArgumentParser());   // parse the type hints in function arguments
    $parser->addParser(new AnnotationParser);   // parse the annotation type declarations
    
    $parser->parse();               // parse the php file
    $parser->getCalls();            // get an array of used classes (with fully qualified namespace)
    $parser->getNamespaces();       // returns an array with namespaces. every item has an 'use' and an 'alias' key
    $parser->getAllUsedNamespaces() // get all used classes (getCalls and getAnnotationCalls) and remove all doubles
    $parser->getNamespace();        // returns null or the current namespace
}
```

TODO:
* remove elements like array, int, string e.t.c. from used classes list.
