<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 30.10.2016
 * Time: 04:36
 */

namespace PHPFileParser\Parser;


use PHPFileParser\ParseInterface;

class StaticParser extends BaseParser implements ParseInterface
{

    public function parse(array $tokens, $index)
    {
        $call = '';
        if ($tokens[$index][0] == T_DOUBLE_COLON) {
            if ($tokens[$index - 1][1] == 'self') {
                return [];
            }

            $index--;
            do {
                $call = $tokens[$index][1] . $call;
                $index--;
            } while (in_array($tokens[$index][0], [T_STRING, T_NS_SEPARATOR]));

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