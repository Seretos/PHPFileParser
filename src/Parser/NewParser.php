<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 30.10.2016
 * Time: 04:27
 */

namespace PHPFileParser\Parser;


use PHPFileParser\ParseInterface;

class NewParser extends BaseParser implements ParseInterface
{
    /**
     * @param array $tokens
     * @param $index
     * @return null|string
     */
    public function parse(array $tokens, $index){
        $call = '';
        if ($tokens[$index][0] == T_NEW) {
            $index++;
            do {
                if(!in_array($tokens[$index][0], [T_COMMENT,T_DOC_COMMENT])){
                    $call .= $tokens[$index][1];
                }
                $index++;
            } while (in_array($tokens[$index][0], [T_WHITESPACE, T_STRING, T_NS_SEPARATOR,T_COMMENT,T_DOC_COMMENT]));

            $call = trim($call);
            $call = $this->convertAddedNamespace($call);
            $call = $this->convertNamespaceAliases($call);
            $call = $this->convertCallToNamespace($call);
            $call = $this->clearNamespace($call);

            return [$call];
        }
        return [];
    }
}