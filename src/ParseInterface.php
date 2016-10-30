<?php
/**
 * Created by PhpStorm.
 * User: arnev
 * Date: 30.10.2016
 * Time: 04:26
 */

namespace PHPFileParser;


interface ParseInterface
{
    /**
     * @param array $namespaces
     */
    public function setNamespaces(array $namespaces);

    /**
     * @param null|string $namespace
     */
    public function setNamespace($namespace);

    /**
     * @param array $tokens
     * @param int $index
     * @return mixed
     */
    public function parse(array $tokens, $index);
}