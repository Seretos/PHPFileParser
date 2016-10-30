<?php
/**
 * Created by PhpStorm.
 * User: Seredos
 * Date: 27.10.2016
 * Time: 21:05
 */

namespace PHPFileParser;


use Symfony\Component\Finder\SplFileInfo;

class PHPFileParser
{
    /**
     * @var SplFileInfo
     */
    private $file;

    /**
     * @var array
     */
    private $namespaces;

    /**
     * @var string[]
     */
    private $calls;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var ParseInterface[]
     */
    private $parsers;

    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
        $this->namespaces = [];
        $this->calls = [];
        $this->parsers = [];
    }

    public function addParser(ParseInterface $parser){
        $this->parsers[] = $parser;
    }

    public function parse()
    {
        $content = $this->file->getContents();
        $tokens = token_get_all($content);

        $this->parseNamespace($tokens);
        $this->parseNamespaces($tokens);
        $this->parseUsedClasses($tokens);

        $count = count($this->calls);
        for($i=0;$i<$count;$i++){
            if(in_array($this->calls[$i],['int','integer','array','boolean','bool','mixed','double','object','resource','void','self','null','parent','static','callable','true','false','string'])
            ||$this->calls[$i] == ''){
                unset($this->calls[$i]);
            }
        }
        $this->calls = array_values($this->calls);
    }

    public function getCalls()
    {
        return $this->calls;
    }

    public function getAllUsedNamespaces(){
        $used = $this->calls;

        return array_values(array_intersect_key($used,array_unique(array_map(function($item){
            return strtolower($item);
        },$used))));
    }

    public function getNamespaces()
    {
        return $this->namespaces;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    private function parseUsedClasses($tokens)
    {
        $this->calls = [];

        foreach($this->parsers as $parser){
            $parser->setNamespace($this->namespace);
            $parser->setNamespaces($this->namespaces);
        }

        foreach ($tokens as $index => $value) {
            foreach($this->parsers as $parser){
                $this->calls = array_merge($this->calls,$parser->parse($tokens,$index));
            }
        }
    }

    private function parseNamespace($tokens)
    {
        $this->namespace = null;
        foreach ($tokens as $index => $value) {
            if ($value[0] == T_NAMESPACE) {
                $this->namespace = '';
                $index++;
                do {
                    $this->namespace .= $tokens[$index][1];
                    $index++;
                } while (in_array($tokens[$index][0], [T_WHITESPACE, T_STRING, T_NS_SEPARATOR]));

                $this->namespace = trim($this->namespace);
            }
        }
    }

    private function parseNamespaces($tokens)
    {
        $this->namespaces = [];
        foreach ($tokens as $index => $value) {
            if ($value[0] == T_USE) {
                $index++;
                $var = ['use' => '', 'alias' => ''];
                $current = 'use';
                do {
                    if ($tokens[$index][0] == T_AS) {
                        $current = 'alias';
                        $index++;
                    }
                    if(!in_array($tokens[$index][0], [T_COMMENT,T_DOC_COMMENT])) {
                        $var[$current] .= $tokens[$index][1];
                    }
                    $index++;
                } while (in_array($tokens[$index][0], [T_WHITESPACE, T_STRING, T_NS_SEPARATOR, T_AS,T_COMMENT,T_DOC_COMMENT]));

                $this->namespaces[] = array_map(function ($item) {
                    return trim($item);
                }, $var);
            }
        }
    }
}