<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 30.10.2016
 * Time: 21:17
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

class PHPPackageParser
{
    public function __construct()
    {
    }

    public function parse(array $classMap){
        $calls = [];

        foreach($classMap as $class => $filePath){
            $finder = new Finder();
            $finder->files()->name(basename($filePath))->in(dirname($filePath));

            foreach($finder as $file) {
                $parser = $this->createParser($file);
                $parser->parse();
                $namespaces = $parser->getAllUsedNamespaces();
                $localClasses = $this->getLocalClasses($class,$classMap);
                foreach($namespaces as $call){
                    if(array_key_exists($call,$localClasses)){
                        $call = $localClasses[$call];
                    }
                    if(!isset($classMap[$call])){
                        if(!isset($calls[$call])){
                            $calls[$call] = [];
                        }
                        $calls[$call][] = $class;
                    }
                }
            }
        }

        return $calls;
    }

    private function getLocalClasses($class, array $classMap){
        $classArr = explode('\\',$class);
        unset($classArr[count($classArr)-1]);
        $classArr = array_values($classArr);
        $namespace = implode('\\',$classArr);

        $localNames = [];
        foreach($classMap as $mapClass => $file){
            if($mapClass != $class && strpos(strtolower($mapClass),strtolower($namespace))===0){
                $name = str_replace($namespace.'\\','',$mapClass);
                if(strpos($name,'\\')===false){
                    $localNames[$name] = $mapClass;
                }
            }
        }
        return $localNames;
    }

    private function createParser($file){
        $parser = new PHPFileParser($file);

        $parser->addParser(new NewParser());        // parse new operations like $var = new MyClass();
        $parser->addParser(new StaticParser());     // parse static operations like MyClass::class;
        $parser->addParser(new ExtendsParser());    // parse extended base classes
        $parser->addParser(new ImplementsParser()); // parse implemented class interfaces
        $parser->addParser(new CatchParser());      // parse the catched exception classes
        $parser->addParser(new ArgumentParser());   // parse the type hints in function arguments
        $parser->addParser(new AnnotationParser()); // parse the annotation type declarations
        $parser->addParser(new InstanceOfParser()); // parse instanceof operations

        return $parser;
    }
}