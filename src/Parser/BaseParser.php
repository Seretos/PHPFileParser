<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 30.10.2016
 * Time: 04:28
 */

namespace PHPFileParser\Parser;


abstract class BaseParser
{
    protected $namespaces;
    protected $namespace;

    public function __construct()
    {
        $this->namespaces = [];
        $this->namespace = null;
    }

    /**
     * @param array $namespaces
     */
    public function setNamespaces(array $namespaces){
        $this->namespaces = $namespaces;
    }

    /**
     * @param null|string $namespace
     */
    public function setNamespace($namespace){
        $this->namespace = $namespace;
    }

    protected function convertAddedNamespace($call)
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

    protected function convertNamespaceAliases($call)
    {
        foreach ($this->namespaces as $namespace) {
            if ($namespace['alias'] != '' && strpos(strtolower($call), strtolower($namespace['alias'])) === 0) {
                $call = str_ireplace($namespace['alias'], $namespace['use'], $call);
            }
        }
        return $call;
    }

    protected function convertCallToNamespace($call)
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

    protected function clearNamespace($call){
        if(strpos($call,'\\') === 0){
            return substr($call,1,strlen($call));
        }
        return $call;
    }
}