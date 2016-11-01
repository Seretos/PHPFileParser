<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 31.10.2016
 * Time: 17:12
 */

namespace PHPFileParser;


use PHPFileParser\Parser\AnnotationParser;
use PHPFileParser\Parser\ArgumentParser;
use PHPFileParser\Parser\CatchParser;
use PHPFileParser\Parser\ExtendsParser;
use PHPFileParser\Parser\ImplementsParser;
use PHPFileParser\Parser\InstanceOfParser;
use PHPFileParser\Parser\NewParser;
use PHPFileParser\Parser\StaticParser;
use Symfony\Component\Finder\Finder;

class Factory
{
    public function createFinder(){
        return new Finder();
    }

    public function createParser($parseAnnotations = true){
        $parser = new PHPFileParser();

        $parser->addParser(new NewParser());        // parse new operations like $var = new MyClass();
        $parser->addParser(new StaticParser());     // parse static operations like MyClass::class;
        $parser->addParser(new ExtendsParser());    // parse extended base classes
        $parser->addParser(new ImplementsParser()); // parse implemented class interfaces
        $parser->addParser(new CatchParser());      // parse the catched exception classes
        $parser->addParser(new ArgumentParser());   // parse the type hints in function arguments
        $parser->addParser(new InstanceOfParser()); // parse instanceof operations
        if($parseAnnotations == true) {
            $parser->addParser(new AnnotationParser()); // parse the annotation type declarations
        }

        return $parser;
    }
}