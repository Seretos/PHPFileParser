<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 30.10.2016
 * Time: 04:43
 */

namespace PHPFileParser\Parser;


use PHPFileParser\ParseInterface;

class ArgumentParser extends BaseParser implements ParseInterface
{

    /**
     * @param array $tokens
     * @param int $index
     * @return mixed
     */
    public function parse(array $tokens, $index)
    {
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
                        $call = trim($lastString);

                        $call = $this->convertAddedNamespace($call);
                        $call = $this->convertNamespaceAliases($call);
                        $call = $this->convertCallToNamespace($call);
                        $call = $this->clearNamespace($call);

                        $calls[] = $call;
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
        return [];
    }
}