<?php
use PHPFileParser\Factory;
use PHPFileParser\PHPPackageParser;
use Symfony\Component\Finder\Finder;

/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 30.10.2016
 * Time: 21:20
 */
class PHPPackageParserTest extends PHPUnit_Framework_TestCase
{
//    /**
//     * @test
//     */
//    public function parse(){
//        $parser = new PHPPackageParser(new Factory());
//        $result = $parser->parse(array (
//            'PHPFileParser\\ParseInterface' => __DIR__.'/../src/ParseInterface.php',
//            'PHPFileParser\\Parser\\AnnotationParser' => __DIR__.'/../src/Parser\\AnnotationParser.php',
//            'PHPFileParser\\Parser\\ArgumentParser' => __DIR__.'/../src/Parser\\ArgumentParser.php',
//            'PHPFileParser\\Parser\\BaseParser' => __DIR__.'/../src/Parser\\BaseParser.php',
//            'PHPFileParser\\Parser\\CatchParser' => __DIR__.'/../src/Parser\\CatchParser.php',
//            'PHPFileParser\\Parser\\ExtendsParser' => __DIR__.'/../src/Parser\\ExtendsParser.php',
//            'PHPFileParser\\Parser\\ImplementsParser' => __DIR__.'/../src/Parser\\ImplementsParser.php',
//            'PHPFileParser\\Parser\\InstanceOfParser' => __DIR__.'/../src/Parser\\InstanceOfParser.php',
//            'PHPFileParser\\Parser\\NewParser' => __DIR__.'/../src/Parser\\NewParser.php',
//            'PHPFileParser\\Parser\\StaticParser' => __DIR__.'/../src/Parser\\StaticParser.php',
//            'PHPFileParser\\PHPFileParser' => __DIR__.'/../src/PHPFileParser.php',
//            'PHPFileParser\\PHPPackageParser' => __DIR__.'/../src/PHPPackageParser.php',
//            'PHPFileParser\\Factory' => __DIR__.'/../src/Factory.php',
//        ));
//
//        $this->assertSame([
//            'Symfony\\Component\\Finder\\SplFileInfo' =>['PHPFileParser\\PHPFileParser']
//            ,'Symfony\\Component\\Finder\\Finder' =>['PHPFileParser\\Factory']],$result);
//    }

    /**
     * @test
     */
    public function parse_selfResult(){
//        $mockFactory = $this->getMockBuilder(Factory::class)->setMethods(['createFinder'])->getMock();
//
//        $mockFinder = $this->getMockBuilder(Finder::class)->disableOriginalConstructor()->getMock();
//        $mockFile = $this->getMockBuilder(\Symfony\Component\Finder\SplFileInfo::class)->disableOriginalConstructor()->getMock();
//
//        $mockFileIterator = $this->getMockBuilder(Iterator::class)->disableOriginalConstructor()->getMock();
//        $mockFileIterator->expects($this->at(0))->method('rewind');
//        $mockFileIterator->expects($this->at(1))->method('valid')->will($this->returnValue(true));
//        $mockFileIterator->expects($this->at(2))->method('current')->will($this->returnValue($mockFile));
//
//        $mockFinder->expects($this->at(0))->method('files')->will($this->returnValue($mockFinder));
//        $mockFinder->expects($this->at(1))->method('name')->with('test.php')->will($this->returnValue($mockFinder));
//        $mockFinder->expects($this->at(2))->method('in')->with('src')->will($this->returnValue($mockFinder));
//
//        $mockFinder->expects($this->at(3))->method('getIterator')->will($this->returnValue($mockFileIterator));
//
//        $mockFactory->expects($this->at(0))->method('createFinder')->will($this->returnValue($mockFinder));

        $mockFactory = $this->createMapMock(['my\\test\\test' => ['src/test.php','<?php
            class test extends BaseTest implements \\other\\package\\TestInerface{
                /**
                 * @return BaseTest
                 */
                public function getSelf(){
                    return $this;
                }
            }
        '],
        'my\\test\\BaseTest' => ['src/BaseTest.php','<?php
            class BaseTest{}
        '],
            'my\\Example' => ['src/my/Example.php','<?php
            class Example{}
        ']]);

        $parser = new PHPPackageParser($mockFactory);

        $result = $parser->parse(['my\\test\\test' => 'src/test.php'
                ,'my\\test\\BaseTest' => 'src/BaseTest.php']);

        $this->assertSame(['other\\package\\TestInerface' => ['my\\test\\test']],$result);
    }

    private function createMapMock($classMap){
        $mockFactory = $this->getMockBuilder(Factory::class)->setMethods(['createFinder'])->getMock();
        //$factoryExpectsIndex = 0;

        $mockFinders = [];

        foreach($classMap as $class => $content){
            $mockFinder = $this->getMockBuilder(Finder::class)->disableOriginalConstructor()->getMock();
            $mockFile = $this->getMockBuilder(\Symfony\Component\Finder\SplFileInfo::class)->disableOriginalConstructor()->getMock();
            $mockFile->expects($this->at(0))->method('getContents')->will($this->returnValue($content[1]));

            $mockFileIterator = $this->getMockBuilder(Iterator::class)->disableOriginalConstructor()->getMock();
            $mockFileIterator->expects($this->at(0))->method('rewind');
            $mockFileIterator->expects($this->at(1))->method('valid')->will($this->returnValue(true));
            $mockFileIterator->expects($this->at(2))->method('current')->will($this->returnValue($mockFile));

            $mockFinder->expects($this->at(0))->method('files')->will($this->returnValue($mockFinder));
            $mockFinder->expects($this->at(1))->method('name')->with(basename($content[0]))->will($this->returnValue($mockFinder));
            $mockFinder->expects($this->at(2))->method('in')->with(dirname($content[0]))->will($this->returnValue($mockFinder));

            $mockFinder->expects($this->at(3))->method('getIterator')->will($this->returnValue($mockFileIterator));

            $mockFinders[] = [$mockFinder];
            //$factoryExpectsIndex+=2;
        }
        $mockFactory->expects($this->any())->method('createFinder')->will($this->returnValueMap($mockFinders));
        return $mockFactory;
    }
}