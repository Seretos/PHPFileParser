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

    public function __construct(SplFileInfo $file)
    {
        $this->file = $file;
        $this->namespaces = [];
        $this->calls = [];
    }

    public function parse()
    {
        $content = $this->file->getContents();
        $tokens = token_get_all($content);

        $this->parseNamespace($tokens);
        $this->parseNamespaces($tokens);
        $this->parseUsedClasses($tokens);
        //TODO parse annotation declarations
    }

    public function getCalls()
    {
        return $this->calls;
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

        foreach ($tokens as $index => $value) {
            if ($call = $this->parseNewCall($tokens, $index)) {
                $call = $this->convertAddedNamespace($call);
                $call = $this->convertNamespaceAliases($call);
                $call = $this->convertCallToNamespace($call);
                $call = $this->convertChildNamespaces($call);
                $this->calls[] = $call;
            } else if ($call = $this->parseStaticCall($tokens, $index)) {
                $call = $this->convertAddedNamespace($call);
                $call = $this->convertNamespaceAliases($call);
                $call = $this->convertCallToNamespace($call);
                $call = $this->convertChildNamespaces($call);
                $this->calls[] = $call;
            } else if ($call = $this->parseExtendsCall($tokens, $index)) {
                $call = $this->convertAddedNamespace($call);
                $call = $this->convertNamespaceAliases($call);
                $call = $this->convertCallToNamespace($call);
                $call = $this->convertChildNamespaces($call);
                $this->calls[] = $call;
            } else if ($call = $this->parseImplementsCall($tokens, $index)) {
                $call = $this->convertAddedNamespace($call);
                $call = $this->convertNamespaceAliases($call);
                $call = $this->convertCallToNamespace($call);
                $call = $this->convertChildNamespaces($call);
                $this->calls[] = $call;
            } else if ($call = $this->parseCatchCall($tokens, $index)) {
                $call = $this->convertAddedNamespace($call);
                $call = $this->convertNamespaceAliases($call);
                $call = $this->convertCallToNamespace($call);
                $call = $this->convertChildNamespaces($call);
                $this->calls[] = $call;
            }
            //TODO: parse function arguments
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
                    $var[$current] .= $tokens[$index][1];
                    $index++;
                } while (in_array($tokens[$index][0], [T_WHITESPACE, T_STRING, T_NS_SEPARATOR, T_AS]));

                $this->namespaces[] = array_map(function ($item) {
                    return trim($item);
                }, $var);
            }
        }
    }

    private function convertAddedNamespace($call)
    {
        if ((strpos($call, '\\') === 0 && $this->namespace == null)
            || (strpos($call, '\\') === false && $this->namespace != null)
        ) {
            if ($this->namespace == null) {
                $call = substr($call, 1, strlen($call));
            }
            //$this->namespaces[] = ['use' => $call, 'alias' => ''];
            return $call;
        }
        return $call;
    }

    private function convertNamespaceAliases($call)
    {
        foreach ($this->namespaces as $namespace) {
            if ($namespace['alias'] != '' && strpos($call, $namespace['alias']) === 0) {
                $call = str_replace($namespace['alias'], $namespace['use'], $call);
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

    private function parseCatchCall($tokens, $index)
    {
        $call = '';
        if ($tokens[$index][0] == T_CATCH) {
            $index++;
            do {
                if (!is_array($tokens[$index])) {
                    $tokens[$index][1] = $tokens[$index];
                }
                if (strpos($tokens[$index][1], '$') !== false) {
                    break;
                }
                if ($tokens[$index][1] != '(') {
                    $call .= $tokens[$index][1];
                }
                $index++;
            } while ($tokens[$index] == '(' || in_array($tokens[$index][0], [T_WHITESPACE, T_STRING, T_NS_SEPARATOR]));
            return trim($call);
        }
        return null;
    }

    private function convertChildNamespaces($call)
    {
        if (strpos($call, '\\') > 0) {
            $subNames = explode('\\', $call);
            $subName = array_shift($subNames);
            foreach ($this->namespaces as $namespace) {
                $compareStr = substr($namespace['use'], strlen($namespace['use']) - strlen($subName) - 1, strlen($namespace['use']));
                if ($compareStr == '\\' . $subName) {
                    $call = $namespace['use'] . '\\' . implode('\\', $subNames);
                }
            }
        }
        return $call;
    }

    private function parseImplementsCall($tokens, $index)
    {
        $call = '';
        if ($tokens[$index][0] == T_IMPLEMENTS) {
            $index++;
            do {
                $call .= $tokens[$index][1];
                $index++;
            } while (in_array($tokens[$index][0], [T_WHITESPACE, T_STRING, T_NS_SEPARATOR]));
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
                $call .= $tokens[$index][1];
                $index++;
            } while (in_array($tokens[$index][0], [T_WHITESPACE, T_STRING, T_NS_SEPARATOR]));
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
                $call .= $tokens[$index][1];
                $index++;
            } while (in_array($tokens[$index][0], [T_WHITESPACE, T_STRING, T_NS_SEPARATOR]));
            return trim($call);
        }
        return null;
    }
}