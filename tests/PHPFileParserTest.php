<?php
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
     * @test
     */
    public function parse_emptyFile()
    {
        $mockSplFileInfo = $this->createFileMock('');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame([], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
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
        class TestExample4 extends aliasClass{}
        ');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
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
            , 'my\\test2\\Example2'], $parser->getCalls());
        $this->assertSame([['use' => 'my\\aliasNamespace', 'alias' => 'aliasNamespace']
            , ['use' => 'my\\test2\\Example2', 'alias' => 'aliasClass']
            , ['use' => 'my\\test2\\MyException', 'alias' => 'aliasException']], $parser->getNamespaces());
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
           
            try{}catch(Exception $e){}
            try{}catch(MyException $e){}
            try{}catch(my\\test2\\MyException $e){}
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
        class TestExample2 extends my\\test2\\Example1{}
        class TestExample3 extends aliasNamespace\\Example1{}
        class TestExample4 extends aliasClass{}
        ');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\test', $parser->getNamespace());
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
            , 'my\\test2\\Example2'], $parser->getCalls());
        $this->assertSame([['use' => 'my\\aliasNamespace', 'alias' => 'aliasNamespace']
            , ['use' => 'my\\test2\\Example2', 'alias' => 'aliasClass']
            , ['use' => 'my\\test2\\MyException', 'alias' => 'aliasException']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_namespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace test;');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('test', $parser->getNamespace());
        $this->assertSame([], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_complexNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace My\\FullNamespace\\test;');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('My\\FullNamespace\\test', $parser->getNamespace());
        $this->assertSame([], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_useNamespaces()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\test;use My\\FullNamespace2\\test;use My\\AliasNamespace\\test as aliasTest;');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame([], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\test', 'alias' => ''], ['use' => 'My\\FullNamespace2\\test', 'alias' => ''], ['use' => 'My\\AliasNamespace\\test', 'alias' => 'aliasTest']], $parser->getNamespaces());
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
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['Test'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_new_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; $test = new Test();');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['Test'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static()
    {
        $mockSplFileInfo = $this->createFileMock('<?php $var = Test::class;');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['Test'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; $var = Test::class;');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['Test'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends()
    {
        $mockSplFileInfo = $this->createFileMock('<?php class test extends BaseTest{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['BaseTest'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; class test extends BaseTest{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['BaseTest'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements()
    {
        $mockSplFileInfo = $this->createFileMock('<?php class test implements TestInterface{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['TestInterface'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; class test implements TestInterface{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['TestInterface'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch()
    {
        $mockSplFileInfo = $this->createFileMock('<?php try{}catch(MyException $e){}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['MyException'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; try{}catch(MyException $e){}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['MyException'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
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
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_new_withFullNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; $test = new My\\FullNamespace\\Test();');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withFullNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php $var = \\My\\FullNamespace\\Test::class;');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withFullNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; $var = My\\FullNamespace\\Test::class;');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withFullNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php class test extends \\My\\FullNamespace\\BaseTest{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withFullNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; class test extends My\\FullNamespace\\BaseTest{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withFullNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php class test implements \\My\\FullNamespace\\TestInterface{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withFullNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; class test implements My\\FullNamespace\\TestInterface{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withFullNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php try{}catch(\\My\\FullNamespace\\MyException $e){}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withFullNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; try{}catch(My\\FullNamespace\\MyException $e){}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $parser->getCalls());
        $this->assertSame([], $parser->getNamespaces());
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
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => '']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_new_withUsedNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\Test; $test = new Test();');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => '']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withUsedNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\Test; $var = Test::class;');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => '']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withUsedNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\Test; $var = Test::class;');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => '']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withUsedNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\BaseTest; class test extends BaseTest{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\BaseTest', 'alias' => '']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withUsedNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\BaseTest; class test extends BaseTest{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\BaseTest', 'alias' => '']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withUsedNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\TestInterface; class test implements TestInterface{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\TestInterface', 'alias' => '']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withUsedNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\TestInterface; class test implements TestInterface{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\TestInterface', 'alias' => '']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withUsedNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\MyException; try{}catch(MyException $e){}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\MyException', 'alias' => '']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withUsedNamespace_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\MyException; try{}catch(MyException $e){}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\MyException', 'alias' => '']], $parser->getNamespaces());
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
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => 'myTest']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_new_withUsedAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\Test as myTest; $test = new myTest();');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => 'myTest']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withUsedAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\Test as myTest; $var = myTest::class;');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => 'myTest']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withUsedAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\Test as myTest; $var = myTest::class;');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\Test', 'alias' => 'myTest']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withUsedAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\BaseTest as MyTest; class test extends MyTest{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\BaseTest', 'alias' => 'MyTest']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withUsedAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\BaseTest as MyTest; class test extends MyTest{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\BaseTest', 'alias' => 'MyTest']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withUsedAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\TestInterface as MyInterface; class test implements MyInterface{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\TestInterface', 'alias' => 'MyInterface']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withUsedAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\TestInterface as MyInterface; class test implements MyInterface{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\TestInterface', 'alias' => 'MyInterface']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withUsedAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace\\MyException as testException; try{}catch(testException $e){}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\MyException', 'alias' => 'testException']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withUsedAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace\\MyException as testException; try{}catch(testException $e){}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace\\MyException', 'alias' => 'testException']], $parser->getNamespaces());
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
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'myTest']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_new_withUsedNamespaceAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace as myTest; $test = new myTest\Test();');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'myTest']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withUsedNamespaceAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace as myTest; $var = myTest\\Test::class;');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'myTest']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_static_withUsedNamespaceAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace as myTest; $var = myTest\\Test::class;');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\Test'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'myTest']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withUsedNamespaceAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace as MyTest; class test extends MyTest\\BaseTest{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'MyTest']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_extends_withUsedNamespaceAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace as MyTest; class test extends MyTest\\BaseTest{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\BaseTest'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'MyTest']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withUsedNamespaceAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace as MyTest; class test implements MyTest\\TestInterface{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'MyTest']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_implements_withUsedNamespaceAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace as MyTest; class test implements MyTest\\TestInterface{}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\TestInterface'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'MyTest']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withUsedNamespaceAlias()
    {
        $mockSplFileInfo = $this->createFileMock('<?php use My\\FullNamespace as testException; try{}catch(testException\\MyException $e){}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame(null, $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'testException']], $parser->getNamespaces());
    }

    /**
     * @test
     */
    public function parse_catch_withUsedNamespaceAlias_withNamespace()
    {
        $mockSplFileInfo = $this->createFileMock('<?php namespace my\\Test; use My\\FullNamespace as testException; try{}catch(testException\\MyException $e){}');
        $parser = new PHPFileParser($mockSplFileInfo);

        $parser->parse();

        $this->assertSame('my\\Test', $parser->getNamespace());
        $this->assertSame(['My\\FullNamespace\\MyException'], $parser->getCalls());
        $this->assertSame([['use' => 'My\\FullNamespace', 'alias' => 'testException']], $parser->getNamespaces());
    }

    private function createFileMock($content)
    {
        $mockSplFileInfo = $this->getMockBuilder(SplFileInfo::class)->disableOriginalConstructor()->getMock();
        $mockSplFileInfo->expects($this->once())->method('getContents')->will($this->returnValue($content));
        return $mockSplFileInfo;
    }
}