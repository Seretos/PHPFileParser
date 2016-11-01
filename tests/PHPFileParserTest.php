<?php
use PHPFileParser\Parser\AnnotationParser;
use PHPFileParser\Parser\ArgumentParser;
use PHPFileParser\Parser\CatchParser;
use PHPFileParser\Parser\ExtendsParser;
use PHPFileParser\Parser\ImplementsParser;
use PHPFileParser\Parser\InstanceOfParser;
use PHPFileParser\Parser\NewParser;
use PHPFileParser\Parser\StaticParser;
use PHPFileParser\PHPFileParser;

use Symfony\Component\Finder\SplFileInfo;

/**
 * Created by PhpStorm.
 * User: Seredos
 * Date: 27.10.2016
 * Time: 21:13
 */
class PHPFileParserTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPFileParser
     */
    private $parser;
    
    protected function setUp()
    {
        parent::setUp();
        $this->parser = $this->initializeParser();
    }

    /**
     * @test
     */
    public function parse_emptyFile()
    {
        $mockSplFileInfo = $this->createFileMock('');

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame([], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_complexExample_caseInsensitive()
    {
        $mockSplFileInfo = $this->createFileMock('<?php
        use my\\aliasnamespace as aliasNamespace /*comment*/;
        use my\\test2\\example2 as /*comment*/ aliasClass;
        use my\\test2\\myexception /*comment*/ as aliasException;

        function myFunction1(){
            $var1 = new example1();
            $var2 = new \\my\\test2\\Example1();
            $var3 = new aliasNamespace\\Example1/*comment*/();
            $var4 = new /*comment*/aliasClass();

            $var5 = Example1::class;
            $var6 = \\my\\test2\\Example1::class;
            $var7 = aliasNamespace\\Example1::class;
            $var8 = /*comment*/aliasClass::class;

            try{}catch(Exception $e){}
            try{}catch(MyException $e){}
            try{}catch(\\my\\test2\\MyException $e){}
            try{}catch(aliasNamespace\\MyException /*comment*/ $e){}
            try{}catch(/*comment*/ aliasexception $e){}

            //$comment = new CommentedCall();
            //$comment2 = new \\my\\test2\\CommentedCall();
            //$comment3 = new aliasNamespace\\CommentedCall();
            //$comment4 = new aliasClass();

            /**
            $comment = new CommentedCall();
            $comment2 = new \\my\\test2\\CommentedCall();
            $comment3 = new aliasNamespace\\CommentedCall();
            $comment4 = new aliasClass();
            */
        }

        class TestExample extends Example1{}
        class TestExample2 extends \\my\\test2\\Example1{}
        class TestExample3 extends aliasNamespace\\Example1{}
        class TestExample4 extends /*comment*/ aliasClass{
            public function myFunction1(array/*test*/ $arg1, Example1 $arg2 //comment1
                                        , \\my\\test2\\Example1 $arg3 = null    //comment2
                                        , aliasNamespace\\Example1 $arg4
                                        , aliasClass $arg5){
            }
        }
        class TestExample5 implements /*comment*/ myInterface{}
        
        if($test instanceof Example1){
        }else if($test instanceof \\my\\test2\\Example1){
        }else if($test instanceof aliasNamespace\\Example1){
        }else if($test instanceof /*comment*/ aliasClass){
        }
        ');
        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['example1'
            , 'my\\test2\\Example1'
            , 'my\\aliasnamespace\\Example1'
            , 'my\\test2\\example2'
            , 'Example1'
            , 'my\\test2\\Example1'
            , 'my\\aliasnamespace\\Example1'
            , 'my\\test2\\example2'
            , 'Exception'
            , 'MyException'
            , 'my\\test2\\MyException'
            , 'my\\aliasnamespace\\MyException'
            , 'my\\test2\\myexception'
            , 'Example1'
            , 'my\\test2\\Example1'
            , 'my\\aliasnamespace\\Example1'
            , 'my\\test2\\example2'
            , 'Example1'
            , 'my\\test2\\Example1'
            , 'my\\aliasnamespace\\Example1'
            , 'my\\test2\\example2'
            ,'myInterface'
            , 'Example1'
            , 'my\\test2\\Example1'
            , 'my\\aliasnamespace\\Example1'
            , 'my\\test2\\example2'], $this->parser->getCalls());
        $this->assertSame(['example1'
            ,'my\\test2\Example1'
            ,'my\\aliasnamespace\\Example1'
            ,'my\\test2\\example2'
            ,'Exception'
            ,'MyException'
            ,'my\\test2\\MyException'
            ,'my\\aliasnamespace\\MyException'
            ,'myInterface'],$this->parser->getAllUsedNamespaces());
        $this->assertSame([['use' => 'my\\aliasnamespace', 'alias' => 'aliasNamespace']
            , ['use' => 'my\\test2\\example2', 'alias' => 'aliasClass']
            , ['use' => 'my\\test2\\myexception', 'alias' => 'aliasException']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_complexExample()
    {
        $mockSplFileInfo = $this->createFileMock('<?php
        use my\\aliasNamespace as aliasNamespace;
        use my\\test2\\Example2 as aliasClass;
        use my\\test2\\MyException as aliasException;

        function myFunction1(){
            $var1 = new Example1();
            $var2 = new \\my\\test2\\Example1();
            $var3 = new aliasNamespace\\Example1();
            $var4 = new aliasClass();

            $var5 = Example1::class;
            $var6 = \\my\\test2\\Example1::class;
            $var7 = aliasNamespace\\Example1::class;
            $var8 = aliasClass::class;

            try{}catch(Exception $e){}
            try{}catch(MyException $e){}
            try{}catch(\\my\\test2\\MyException $e){}
            try{}catch(aliasNamespace\\MyException $e){}
            try{}catch(aliasException $e){}

            //$comment = new CommentedCall();
            //$comment2 = new \\my\\test2\\CommentedCall();
            //$comment3 = new aliasNamespace\\CommentedCall();
            //$comment4 = new aliasClass();

            /**
            $comment = new CommentedCall();
            $comment2 = new \\my\\test2\\CommentedCall();
            $comment3 = new aliasNamespace\\CommentedCall();
            $comment4 = new aliasClass();
            */
        }

        class TestExample extends Example1{}
        class TestExample2 extends \\my\\test2\\Example1{}
        class TestExample3 extends aliasNamespace\\Example1{}
        class TestExample4 extends aliasClass{
            public function myFunction1(array $arg1, Example1 $arg2
                                        , \\my\\test2\\Example1 $arg3 = null
                                        , aliasNamespace\\Example1 $arg4
                                        , aliasClass $arg5){
                  throw new \\LogicException();
            }
        }
        ');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['Example1'
            , 'my\\test2\\Example1'
            , 'my\\aliasNamespace\\Example1'
            , 'my\\test2\\Example2'
            , 'Example1'
            , 'my\\test2\\Example1'
            , 'my\\aliasNamespace\\Example1'
            , 'my\\test2\\Example2'
            , 'Exception'
            , 'MyException'
            , 'my\\test2\\MyException'
            , 'my\\aliasNamespace\\MyException'
            , 'my\\test2\\MyException'
            , 'Example1'
            , 'my\\test2\\Example1'
            , 'my\\aliasNamespace\\Example1'
            , 'my\\test2\\Example2'
            , 'Example1'
            , 'my\\test2\\Example1'
            , 'my\\aliasNamespace\\Example1'
            , 'my\\test2\\Example2'
            ,'LogicException'], $this->parser->getCalls());
        $this->assertSame([['use' => 'my\\aliasNamespace', 'alias' => 'aliasNamespace']
            , ['use' => 'my\\test2\\Example2', 'alias' => 'aliasClass']
            , ['use' => 'my\\test2\\MyException', 'alias' => 'aliasException']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_complexExample_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php
        namespace my\\test;

        use my\\aliasNamespace as aliasNamespace;
        use my\\test2\\Example2 as aliasClass;
        use my\\test2\\MyException as aliasException;

        function myFunction1(){
            $var1 = new Example1();
            $var2 = new my\\test2\\Example1();
            $var3 = new aliasNamespace\\Example1();
            $var4 = new aliasClass();

            $var5 = Example1::class;
            $var6 = my\\test2\\Example1::class;
            $var7 = aliasNamespace\\Example1::class;
            $var8 = aliasClass::class;

            try{}catch(      Exception $e){}
            try{}catch(MyException $e){}
            try{}catch(my\\test2\\MyException $e){}
            try{}catch(aliasNamespace\\MyException $e){}
            try{}catch(aliasException $e){}

            //$comment = new CommentedCall();
            //$comment2 = new my\\test2\\CommentedCall();
            //$comment3 = new aliasNamespace\\CommentedCall();
            //$comment4 = new aliasClass();

            /**
            $comment = new CommentedCall();
            $comment2 = new my\\test2\\CommentedCall();
            $comment3 = new aliasNamespace\\CommentedCall();
            $comment4 = new aliasClass();
            */
        }

        class TestExample extends Example1{
            public function myFunction2(){
                $var1 = self::class;
            }
        }
        class TestExample2 extends my\\test2\\Example1{}
        class TestExample3 extends aliasNamespace\\Example1{}
        class TestExample4 extends aliasClass{
                public function myFunction1(array $arg1, Example1 $arg2
                                        , my\\test2\\Example1 $arg3 = null
                                        , aliasNamespace\\Example1 $arg4
                                        , aliasClass $arg5){
                    throw new \\LogicException();
            }
        }
        ');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\test', $this->parser->getNamespace());
        $this->assertSame(['Example1'
            , 'my\\test2\\Example1'
            , 'my\\aliasNamespace\\Example1'
            , 'my\\test2\\Example2'
            , 'Example1'
            , 'my\\test2\\Example1'
            , 'my\\aliasNamespace\\Example1'
            , 'my\\test2\\Example2'
            , 'Exception'
            , 'MyException'
            , 'my\\test2\\MyException'
            , 'my\\aliasNamespace\\MyException'
            , 'my\\test2\\MyException'
            , 'Example1'
            , 'my\\test2\\Example1'
            , 'my\\aliasNamespace\\Example1'
            , 'my\\test2\\Example2'
            , 'Example1'
            , 'my\\test2\\Example1'
            , 'my\\aliasNamespace\\Example1'
            , 'my\\test2\\Example2'
        , 'LogicException'], $this->parser->getCalls());
        $this->assertSame([['use' => 'my\\aliasNamespace', 'alias' => 'aliasNamespace']
            , ['use' => 'my\\test2\\Example2', 'alias' => 'aliasClass']
            , ['use' => 'my\\test2\\MyException', 'alias' => 'aliasException']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_namespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace test;');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('test', $this->parser->getNamespace());
        $this->assertSame([], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_complexNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace My\\FullNamespace\\test;');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('My\\FullNamespace\\test', $this->parser->getNamespace());
        $this->assertSame([], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_useNamespaces()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\test;use My\\FullNamespace2\\test;use My\\AliasNamespace\\test as aliasTest;');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame([], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\test', 'alias' => ''], ['use' => 'My\\FullNamespace2\\test', 'alias' => ''], ['use' => 'My\\AliasNamespace\\test', 'alias' => 'aliasTest']], $this->parser->getNamespaces());
    }

    /**
     * Parse Simple namespace operations
     */
    /**
     * @test
     */
    public function parse_new()
    {
        $mockSplFileInfo = $this->createFileMock('<?php $test = new Test();');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['Test'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_new_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; $test = new Test();');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['Test'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static()
    {
        $mockSplFileInfo = $this->createFileMock('<?php $var = Test::class;');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['Test'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; $var = Test::class;');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['Test'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends()
    {
        $mockSplFileInfo = $this->createFileMock('<?php class test extends BaseTest{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['BaseTest'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; class test extends BaseTest{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['BaseTest'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements()
    {
        $mockSplFileInfo = $this->createFileMock('<?php class test implements TestInterface{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['TestInterface'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; class test implements TestInterface{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['TestInterface'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch()
    {
        $mockSplFileInfo = $this->createFileMock('<?php try{}catch(MyException $e){}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['MyException'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; try{}catch(MyException $e){}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['MyException'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_function()
    {
        $mockSplFileInfo = $this->createFileMock('<?php function myFunction(MyFunctionArgument1 $arg1, MyFunctionArgument2 $arg2, array $arg3)');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['MyFunctionArgument1','MyFunctionArgument2'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_function_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; function myFunction(MyFunctionArgument1 $arg1, MyFunctionArgument2 $arg2, array $arg3)');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['MyFunctionArgument1','MyFunctionArgument2'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parseAnnotation(){
        $mockSplFileInfo = $this->createFileMock('<?php
            use my\\aliasNamespace as aliasNamespace;
            use my\\test2\\Example2 as aliasClass;

            /**
             * @param Example1 $arg1
             * @param \\my\\FullNamespace\\Example1 $arg2
             * @param aliasNamespace\\Example1 $arg3
             * @param aliasClass $arg4
             * @return \\my\\FullNamespace\\Example1
             */
            function myFunction($arg1, $arg2, $arg3, $arg4){
                /* @var $var1 Example1*/
                $var1 = null;
                /* @var $var2 \\my\\FullNamespace\\Example1*/
                $var2 = null;
                /* @var $var3 aliasNamespace\\Example1*/
                $var3 = null;
                /* @var $var4 aliasClass*/
                $var4 = null;
                /* @var $var5 array */
                $var5 = [];
                /* @var $var6 mixed */
                $var6 = [];
            }');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['Example1'
            ,'my\\FullNamespace\\Example1'
            ,'my\\aliasNamespace\\Example1'
            ,'my\\test2\\Example2'
            ,'my\\FullNamespace\\Example1'
            ,'Example1'
            ,'my\\FullNamespace\\Example1'
            ,'my\\aliasNamespace\\Example1'
            ,'my\\test2\\Example2'
            ,'array'
            ,'mixed'], $this->parser->getCalls());
        $this->assertSame([['use' => 'my\\aliasNamespace','alias' => 'aliasNamespace'],['use' => 'my\\test2\\Example2','alias' => 'aliasClass']], $this->parser->getNamespaces());
    }

    /**
     * Parse full namespace operations
     */
    /**
     * @test
     */
    public function parse_new_withFullNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php $test = new \\My\\FullNamespace\\Test();');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_new_withFullNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; $test = new My\\FullNamespace\\Test();');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withFullNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php $var = \\My\\FullNamespace\\Test::class;');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withFullNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; $var = My\\FullNamespace\\Test::class;');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withFullNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php class test extends \\My\\FullNamespace\\BaseTest{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withFullNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; class test extends My\\FullNamespace\\BaseTest{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withFullNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php class test implements \\My\\FullNamespace\\TestInterface{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withFullNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; class test implements My\\FullNamespace\\TestInterface{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withFullNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php try{}catch(\\My\\FullNamespace\\MyException $e){}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withFullNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; try{}catch(My\\FullNamespace\\MyException $e){}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_function_withFullNamespaces()
    {
        $mockSplFileInfo = $this->createFileMock('<?php function myFunction(\\my\\FullNamespace\\MyFunctionArgument1 $arg1, \\my\\FullNamespace\\MyFunctionArgument2 $arg2, array $arg3)');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['my\\FullNamespace\\MyFunctionArgument1','my\\FullNamespace\\MyFunctionArgument2'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_function_withFullNamespaces_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; function myFunction(my\\FullNamespace\\MyFunctionArgument1 $arg1, my\\FullNamespace\\MyFunctionArgument2 $arg2, array $arg3)');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['my\\FullNamespace\\MyFunctionArgument1','my\\FullNamespace\\MyFunctionArgument2'], $this->parser->getCalls());
        $this->assertSame([], $this->parser->getNamespaces());
    }

    /**
     * Parse used namespace operations
     */
    /**
     * @test
     */
    public function parse_new_withUsedNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\Test; $test = new Test();');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => '']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_new_withUsedNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\Test; $test = new Test();');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => '']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withUsedNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\Test; $var = Test::class;');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => '']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withUsedNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\Test; $var = Test::class;');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => '']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withUsedNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\BaseTest; class test extends BaseTest{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\BaseTest', 'alias' => '']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withUsedNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\BaseTest; class test extends BaseTest{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\BaseTest', 'alias' => '']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withUsedNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\TestInterface; class test implements TestInterface{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\TestInterface', 'alias' => '']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withUsedNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\TestInterface; class test implements TestInterface{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\TestInterface', 'alias' => '']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withUsedNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\MyException; try{}catch(MyException $e){}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\MyException', 'alias' => '']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withUsedNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\MyException; try{}catch(MyException $e){}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\MyException', 'alias' => '']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_function_withUsedNamespaces()
    {
        $mockSplFileInfo = $this->createFileMock('<?php
            use my\\FullNamespace\\MyFunctionArgument1;
            use my\\FullNamespace\\MyFunctionArgument2;

            function myFunction(MyFunctionArgument1 $arg1, MyFunctionArgument2 $arg2, array $arg3)');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['my\\FullNamespace\\MyFunctionArgument1','my\\FullNamespace\\MyFunctionArgument2'], $this->parser->getCalls());
        $this->assertSame([['use' => 'my\\FullNamespace\\MyFunctionArgument1','alias' => ''],
            ['use' => 'my\\FullNamespace\\MyFunctionArgument2','alias' => '']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_function_withUsedNamespaces_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php
            namespace my\\Test;

            use my\\FullNamespace\\MyFunctionArgument1;
            use my\\FullNamespace\\MyFunctionArgument2;

            function myFunction(MyFunctionArgument1 $arg1, MyFunctionArgument2 $arg2, array $arg3)');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['my\\FullNamespace\\MyFunctionArgument1','my\\FullNamespace\\MyFunctionArgument2'], $this->parser->getCalls());
        $this->assertSame([['use' => 'my\\FullNamespace\\MyFunctionArgument1','alias' => ''],
            ['use' => 'my\\FullNamespace\\MyFunctionArgument2','alias' => '']], $this->parser->getNamespaces());
    }

    /**
     * Parse namespace class alias operations
     */
    /**
     * @test
     */
    public function parse_new_withUsedAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\Test as myTest; $test = new myTest();');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => 'myTest']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_new_withUsedAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\Test as myTest; $test = new myTest();');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => 'myTest']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withUsedAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\Test as myTest; $var = myTest::class;');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => 'myTest']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withUsedAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\Test as myTest; $var = myTest::class;');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => 'myTest']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withUsedAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\BaseTest as MyTest; class test extends MyTest{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\BaseTest', 'alias' => 'MyTest']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withUsedAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\BaseTest as MyTest; class test extends MyTest{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\BaseTest', 'alias' => 'MyTest']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withUsedAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\TestInterface as MyInterface; class test implements MyInterface{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\TestInterface', 'alias' => 'MyInterface']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withUsedAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\TestInterface as MyInterface; class test implements MyInterface{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\TestInterface', 'alias' => 'MyInterface']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withUsedAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\MyException as testException; try{}catch(testException $e){}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\MyException', 'alias' => 'testException']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withUsedAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\MyException as testException; try{}catch(testException $e){}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\MyException', 'alias' => 'testException']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_function_withUsedAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php
            use my\\FullNamespace\\MyFunctionArgument1 as Arg1;
            use my\\FullNamespace\\MyFunctionArgument2 as Arg2;

            function myFunction(array $arg3 = [],Arg1 $arg1, Arg2 $arg2)');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['my\\FullNamespace\\MyFunctionArgument1','my\\FullNamespace\\MyFunctionArgument2'], $this->parser->getCalls());
        $this->assertSame([['use' => 'my\\FullNamespace\\MyFunctionArgument1','alias' => 'Arg1'],
            ['use' => 'my\\FullNamespace\\MyFunctionArgument2','alias' => 'Arg2']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_function_withUsedAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php
            namespace my\\Test;

            use my\\FullNamespace\\MyFunctionArgument1 as Arg1;
            use my\\FullNamespace\\MyFunctionArgument2 as Arg2;

            function myFunction(array $arg3 = null,Arg1 $arg1, Arg2 $arg2)');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['my\\FullNamespace\\MyFunctionArgument1','my\\FullNamespace\\MyFunctionArgument2'], $this->parser->getCalls());
        $this->assertSame([['use' => 'my\\FullNamespace\\MyFunctionArgument1','alias' => 'Arg1'],
            ['use' => 'my\\FullNamespace\\MyFunctionArgument2','alias' => 'Arg2']], $this->parser->getNamespaces());
    }

    /**
     * Parse namespace alias operations
     */
    /**
     * @test
     */
    public function parse_new_withUsedNamespaceAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace as myTest; $test = new myTest\Test();');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'myTest']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_new_withUsedNamespaceAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace as myTest; $test = new myTest\Test();');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'myTest']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withUsedNamespaceAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace as myTest; $var = myTest\\Test::class;');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'myTest']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withUsedNamespaceAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace as myTest; $var = myTest\\Test::class;');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'myTest']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withUsedNamespaceAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace as MyTest; class test extends MyTest\\BaseTest{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'MyTest']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withUsedNamespaceAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace as MyTest; class test extends MyTest\\BaseTest{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'MyTest']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withUsedNamespaceAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace as MyTest; class test implements MyTest\\TestInterface{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'MyTest']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withUsedNamespaceAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace as MyTest; class test implements MyTest\\TestInterface{}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'MyTest']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withUsedNamespaceAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace as testException; try{}catch(testException\\MyException $e){}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'testException']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withUsedNamespaceAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace as testException; try{}catch(testException\\MyException $e){}');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $this->parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'testException']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_function_withUsedNamespaceAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php
            use my\\FullNamespace as Args;

            function myFunction(Args\\MyFunctionArgument1 $arg1, Args\\MyFunctionArgument2 $arg2, array $arg3)');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame(null, $this->parser->getNamespace());
        $this->assertSame(['my\\FullNamespace\\MyFunctionArgument1','my\\FullNamespace\\MyFunctionArgument2'], $this->parser->getCalls());
        $this->assertSame([['use' => 'my\\FullNamespace','alias' => 'Args']], $this->parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_function_withUsedNamespaceAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php
            namespace my\\Test;

            use my\\FullNamespace as Args;

            function myFunction(Args\\MyFunctionArgument1 $arg1, Args\\MyFunctionArgument2 $arg2, array $arg3)');
        

        $this->parser->parse($mockSplFileInfo);

        $this->assertSame('my\\Test', $this->parser->getNamespace());
        $this->assertSame(['my\\FullNamespace\\MyFunctionArgument1','my\\FullNamespace\\MyFunctionArgument2'], $this->parser->getCalls());
        $this->assertSame([['use' => 'my\\FullNamespace','alias' => 'Args']], $this->parser->getNamespaces());
    }

    private function initializeParser(){
        $this->parser = new PHPFileParser();

        $this->parser->addParser(new NewParser());
        $this->parser->addParser(new StaticParser());
        $this->parser->addParser(new ExtendsParser());
        $this->parser->addParser(new ImplementsParser());
        $this->parser->addParser(new CatchParser());
        $this->parser->addParser(new ArgumentParser());
        $this->parser->addParser(new InstanceOfParser());
        $this->parser->addParser(new AnnotationParser());

        return $this->parser;
    }

    private function createFileMock($content)
    {
        $mockSplFileInfo = $this->getMockBuilder(SplFileInfo::class)->disableOriginalConstructor()->getMock();
        $mockSplFileInfo->expects($this->once())->method('getContents')->will($this->returnValue($content));
        return $mockSplFileInfo;
    }
}