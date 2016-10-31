<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 30.10.2016
 * Time: 21:17
 */

namespace PHPFileParser;


class PHPPackageParser
{
    private $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function parse(array $classMap, $parseAnnotations = true){
        $calls = [];

        foreach($classMap as $class => $filePath){
            $finder = $this->factory->createFinder();
            $finder->files();
            $finder->name(basename($filePath));
            $finder->in(dirname($filePath));
            $files = $finder->getIterator();

            foreach($files as $file) {
                $parser = $this->factory->createParser($file,$parseAnnotations);
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

        foreach($calls as $class => $sources){
            if(in_array($class,[
                    'int'
                    ,'integer'
                    ,'array'
                    ,'boolean'
                    ,'bool'
                    ,'mixed'
                    ,'double'
                    ,'object'
                    ,'resource'
                    ,'void'
                    ,'self'
                    ,'null'
                    ,'parent'
                    ,'static'
                    ,'callable'
                    ,'true'
                    ,'false'
                    ,'string'
                    ,'numeric'
                    ,'number'
                    ,'float'
                    ,'DOMXPath'
                    ,'DOMText'
                    ,'DOMNodeList'
                    ,'DOMNode'
                    ,'DOMDocument'
                    ,'DOMElement'
                    ,'DOMCharacterData'
                    ,'ReflectionFunction'
                    ,'__PHP_Incomplete_Class'
                    ,'ErrorException'
                    ,'Closure'
                    ,'Phar'
                    ,'RecursiveIterator'
                    ,'RecursiveFilterIterator'
                    ,'FilterIterator'
                    ,'InvalidArgumentException'
                    ,'ReflectionMethod'
                    ,'ReflectionClass'
                    ,'ReflectionProperty'
                    ,'Iterator'
                    ,'LogicException'
                    ,'SplObjectStorage'
                    ,'IteratorAggregate'
                    ,'ReflectionException'
                    ,'Countable'
                    ,'Traversable'
                    ,'ArrayAccess'
                    ,'Throwable'
                    ,'RecursiveIteratorIterator'
                    ,'RuntimeException'
                    ,'ReflectionParameter'
                ,'ReflectionObject'
                ,'Exception'
                ,'ArrayObject'
                ,'Reflector'
                ,'XMLWriter'
                ,'OutOfBonesException'
                ,'RecursiveDirectoryIterator'
                ,'AppendIterator'
                ,'SoapClient'
                ,'BadMethodCallException'
                ,'UnexpectedValueException'
                ,'DateInterval'
                ,'DateTimeInterface'
                ,'DateTime'
                ,'SplFixedArray'
                ,'stdClass'
                ,'Serializable'
                ,'FilesystemIterator'
                ,'Error'])
                ||$class == ''){
                unset($calls[$class]);
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
            if($mapClass != $class && $namespace != '' && strpos(strtolower($mapClass),strtolower($namespace))===0){
                $name = str_replace($namespace.'\\','',$mapClass);
                if(strpos($name,'\\')===false){
                    $localNames[$name] = $mapClass;
                }
            }
        }
        return $localNames;
    }
}