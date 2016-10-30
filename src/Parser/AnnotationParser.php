<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 30.10.2016
 * Time: 05:04
 */

namespace PHPFileParser\Parser;


use PHPFileParser\ParseInterface;

class AnnotationParser extends BaseParser implements ParseInterface
{

    /**
     * @param array $tokens
     * @param int $index
     * @return mixed
     */
    public function parse(array $tokens, $index)
    {
        if($tokens[$index][0] == T_COMMENT||$tokens[$index][0] == T_DOC_COMMENT){
            preg_match_all('/(@var|@param|@return)\s+?(\$?[\w\\\\]*)\s+?([\w\\\\]*)/', $tokens[$index][1], $result);

            if(count($result) > 2){
                $calls = [];
                for($i = 0;$i< count($result[2]); $i++){
                    $call = $result[2][$i];
                    if(substr($result[2][$i],0,1) == '$'){
                        $call = $result[3][$i];
                    }
                    $call = $this->convertAddedNamespace($call);
                    $call = $this->convertNamespaceAliases($call);
                    $call = $this->convertCallToNamespace($call);
                    $calls[] = $call;
                }
                return $calls;
            }
        }
        return [];
    }
}