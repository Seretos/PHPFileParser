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
     * @var string[]
     */
    private $annotationCalls;

    /**
     * @var string
     */
    private $namespace;

    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
        $this->namespaces = [];
        $this->calls = [];
        $this->annotationCalls = [];
    }

    public function parse()
    {
        $content = $this->file->getContents();
        $tokens = token_get_all($content);

        $this->parseNamespace($tokens);
        $this->parseNamespaces($tokens);
        $this->parseUsedClasses($tokens);
        $this->parseAnnotations($tokens);
    }

    public function getCalls()
    {
        return $this->calls;
    }

    public function getAllUsedNamespaces(){
        $used = $this->calls;
        $used = array_merge($used,$this->annotationCalls);

        return array_values(array_intersect_key($used,array_unique(array_map(function($item){
            return strtolower($item);
        },$used))));
    }

    public function getAnnotationCalls(){
        return $this->annotationCalls;
    }

    public function getNamespaces()
    {
        return $this->namespaces;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    private function parseAnnotations($tokens){
        $this->annotationCalls = [];
        foreach($tokens as $index => $value){
            if($value[0] == T_COMMENT||$value[0] == T_DOC_COMMENT){
                preg_match_all('/(@var|@param|@return)\s+?(\$?[\w\\\\]*)\s+?([\w\\\\]*)/', $value[1], $result);

                if(count($result) > 2){
                    for($i = 0;$i< count($result[2]); $i++){
                        $call = $result[2][$i];
                        if(substr($result[2][$i],0,1) == '$'){
                            $call = $result[3][$i];
                        }
                        $call = $this->convertAddedNamespace($call);
                        $call = $this->convertNamespaceAliases($call);
                        $call = $this->convertCallToNamespace($call);

                        $this->annotationCalls[] = $call;
                    }
                }
            }
        }
    }

    private function parseUsedClasses($tokens)
    {
        $this->calls = [];

        foreach ($tokens as $index => $value) {
            if ($call = $this->parseNewCall($tokens, $index)) {
                $call = $this->convertAddedNamespace($call);
                $call = $this->convertNamespaceAliases($call);
                $call = $this->convertCallToNamespace($call);
                $this->calls[] = $call;
            } else if ($call = $this->parseStaticCall($tokens, $index)) {
                $call = $this->convertAddedNamespace($call);
                $call = $this->convertNamespaceAliases($call);
                $call = $this->convertCallToNamespace($call);
                $this->calls[] = $call;
            } else if ($call = $this->parseExtendsCall($tokens, $index)) {
                $call = $this->convertAddedNamespace($call);
                $call = $this->convertNamespaceAliases($call);
                $call = $this->convertCallToNamespace($call);
                $this->calls[] = $call;
            } else if ($call = $this->parseImplementsCall($tokens, $index)) {
                $call = $this->convertAddedNamespace($call);
                $call = $this->convertNamespaceAliases($call);
                $call = $this->convertCallToNamespace($call);
                $this->calls[] = $call;
            } else if ($call = $this->parseCatchCall($tokens, $index)) {
                $call = $this->convertAddedNamespace($call);
                $call = $this->convertNamespaceAliases($call);
                $call = $this->convertCallToNamespace($call);
                $this->calls[] = $call;
            } else if($calls = $this->parseFunctionCall($tokens,$index)){
                for($i=0;$i<count($calls);$i++){
                    $calls[$i] = $this->convertAddedNamespace($calls[$i]);
                    $calls[$i] = $this->convertNamespaceAliases($calls[$i]);
                    $calls[$i] = $this->convertCallToNamespace($calls[$i]);
                }
                $this->calls = array_merge($this->calls,$calls);
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

    private function parseImplementsCall($tokens, $index)
    {
        $call = '';
        if ($tokens[$index][0] == T_IMPLEMENTS) {
            $index++;
            do {
                if(!in_array($tokens[$index][0], [T_COMMENT,T_DOC_COMMENT])){
                    $call .= $tokens[$index][1];
                }
                $index++;
            } while (in_array($tokens[$index][0], [T_WHITESPACE, T_STRING, T_NS_SEPARATOR,T_COMMENT,T_DOC_COMMENT]));
            return trim($call);
        }
        return null;
    }

    private function parseExtendsCall($tokens, $index)
    {
        $call = '';
        if ($tokens[$index][0] == T_EXTENDS) {
            $index++;
            do {
                if(!in_array($tokens[$index][0], [T_COMMENT,T_DOC_COMMENT])){
                    $call .= $tokens[$index][1];
                }
                $index++;
            } while (in_array($tokens[$index][0], [T_WHITESPACE, T_STRING, T_NS_SEPARATOR,T_COMMENT,T_DOC_COMMENT]));
            return trim($call);
        }

        return null;
    }

    private function parseStaticCall($tokens, $index)
    {
        $call = '';
        if ($tokens[$index][0] == T_DOUBLE_COLON) {
            if ($tokens[$index - 1][1] == 'self') {
                return null;
            }

            $index--;
            do {
                $call = $tokens[$index][1] . $call;
                $index--;
            } while (in_array($tokens[$index][0], [T_STRING, T_NS_SEPARATOR]));
            return trim($call);
        }
        return null;
    }

    private function parseNewCall($tokens, $index)
    {
        $call = '';
        if ($tokens[$index][0] == T_NEW) {
            $index++;
            do {
                if(!in_array($tokens[$index][0], [T_COMMENT,T_DOC_COMMENT])){
                    $call .= $tokens[$index][1];
                }
                $index++;
            } while (in_array($tokens[$index][0], [T_WHITESPACE, T_STRING, T_NS_SEPARATOR,T_COMMENT,T_DOC_COMMENT]));
            return trim($call);
        }
        return null;
    }

    private function parseCatchCall($tokens, $index)
    {
        $call = '';
        if ($tokens[$index][0] == T_CATCH) {
            $index++;
            do {
                if (!is_array($tokens[$index])) {
                    $tokens[$index][1] = $tokens[$index];
                }
                if ($tokens[$index][1] != '(') {
                    if(!in_array($tokens[$index][0], [T_COMMENT,T_DOC_COMMENT])){
                        $call .= $tokens[$index][1];
                    }
                }
                $index++;
            } while ($tokens[$index] == '(' || in_array($tokens[$index][0], [T_WHITESPACE, T_STRING, T_NS_SEPARATOR,T_DOC_COMMENT,T_COMMENT]));
            return trim($call);
        }
        return null;
    }

    private function parseFunctionCall($tokens, $index){
        if($tokens[$index][0] == T_FUNCTION){
            $index += 1;
            $started = false;
            $lastString = '';
            $calls = [];
            do{
                if($started){
                    if($tokens[$index][0] == T_STRING||$tokens[$index][0] == T_NS_SEPARATOR){
                        $lastString .= $tokens[$index][1];
                    }else if($tokens[$index][0] == T_VARIABLE && $lastString != ''){
                        $calls[] = trim($lastString);
                        $lastString = '';
                    }else if($tokens[$index] == ','){
                        $lastString = '';
                    }
                }
                if($tokens[$index] == '('){
                    $started = true;
                }
                $index++;
            }while(in_array($tokens[$index],['(',',','=','[',']'])||in_array($tokens[$index][0],[T_NS_SEPARATOR,T_ARRAY,T_WHITESPACE,T_STRING,T_VARIABLE,T_WHILE,T_COMMENT,T_DOC_COMMENT]));
            return $calls;
        }
        return null;
    }

    private function convertAddedNamespace($call)
    {
        if ((strpos($call, '\\') === 0 && $this->namespace == null)
            || (strpos($call, '\\') === false && $this->namespace != null)
        ) {
            if ($this->namespace == null) {
                $call = substr($call, 1, strlen($call));
            }
            return $call;
        }
        return $call;
    }

    private function convertNamespaceAliases($call)
    {
        foreach ($this->namespaces as $namespace) {
            if ($namespace['alias'] != '' && strpos(strtolower($call), strtolower($namespace['alias'])) === 0) {
                $call = str_ireplace($namespace['alias'], $namespace['use'], $call);
            }
        }
        return $call;
    }

    private function convertCallToNamespace($call)
    {
        if (strpos($call, '\\') === false) {
            foreach ($this->namespaces as $namespace) {
                if ($namespace['alias'] == '') {
                    $length = strlen($namespace['use']) - strlen($call) - 1;
                    if ($length > 0) {
                        if (strpos($namespace['use'], '\\' . $call, $length) == $length) {
                            $call = $namespace['use'];
                        }
                    }
                }
            }
        }
        return $call;
    }
}