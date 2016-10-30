<?php
use PHPFileParser\PHPPackageParser;

/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 30.10.2016
 * Time: 21:20
 */
class PHPPackageParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPPackageParser
     */
    private $parser;

    protected function setUp()
    {
        parent::setUp();
        $this->parser = new PHPPackageParser();
    }

    /**
     * @test
     */
    public function parse(){
        $result = $this->parser->parse(array (
            'PHPFileParser\\ParseInterface' => __DIR__.'/../src/ParseInterface.php',
            'PHPFileParser\\Parser\\AnnotationParser' => __DIR__.'/../src/Parser\\AnnotationParser.php',
            'PHPFileParser\\Parser\\ArgumentParser' => __DIR__.'/../src/Parser\\ArgumentParser.php',
            'PHPFileParser\\Parser\\BaseParser' => __DIR__.'/../src/Parser\\BaseParser.php',
            'PHPFileParser\\Parser\\CatchParser' => __DIR__.'/../src/Parser\\CatchParser.php',
            'PHPFileParser\\Parser\\ExtendsParser' => __DIR__.'/../src/Parser\\ExtendsParser.php',
            'PHPFileParser\\Parser\\ImplementsParser' => __DIR__.'/../src/Parser\\ImplementsParser.php',
            'PHPFileParser\\Parser\\InstanceOfParser' => __DIR__.'/../src/Parser\\InstanceOfParser.php',
            'PHPFileParser\\Parser\\NewParser' => __DIR__.'/../src/Parser\\NewParser.php',
            'PHPFileParser\\Parser\\StaticParser' => __DIR__.'/../src/Parser\\StaticParser.php',
            'PHPFileParser\\PHPFileParser' => __DIR__.'/../src/PHPFileParser.php',
            'PHPFileParser\\PHPPackageParser' => __DIR__.'/../src/PHPPackageParser.php',
        ));

        $this->assertSame([
            'Symfony\\Component\\Finder\\SplFileInfo' =>['PHPFileParser\\PHPFileParser']
            ,'Symfony\\Component\\Finder\\Finder' =>['PHPFileParser\\PHPPackageParser']],$result);
    }
}