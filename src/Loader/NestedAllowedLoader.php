<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Loader;

use Gea\Accessor\AccessorInterface;
use Gea\Parser\ParserInterface;
use Gea\Variable\VariableInterface;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class NestedAllowedLoader implements LoaderInterface
{
    /**
     * @var \Gea\Parser\ParserInterface
     */
    private $parser;

    /**
     * @var \Gea\Accessor\AccessorInterface
     */
    private $accessor;

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @param \Gea\Parser\ParserInterface     $parser
     * @param \Gea\Accessor\AccessorInterface $accessor
     */
    public function __construct(ParserInterface $parser, AccessorInterface $accessor)
    {
        $this->parser = $parser;
        $this->accessor = $accessor;
    }

    /**
     * Get an array of variable instances from parser, and loops through them to resolve
     * nested variables.
     * Returns the name of all variables loaded.
     */
    public function load()
    {
        if ($this->loaded) {
            throw new \RuntimeException(
                'Variables already loaded, to override variables, flush existing vars first.'
            );
        }

        $nested = $names = [];
        $parsed = $this->parser->parse();

        array_walk($parsed, function (VariableInterface $var) use (&$nested, &$names) {
            $valid = $var->isValid();
            if ($valid && $var->isNested()) {
                $nested[] = $var;
            } elseif ($valid) {
                $this->accessor->write($var['name'], $var['value']);
                $names[] = $var['name'];
            }
        });

        array_walk($nested, function (VariableInterface $var) use (&$names) {
            $value = array_reduce($var['nested'], [$this, 'resolveNested'], $var['value']);
            $this->accessor->write($var['name'], $value);
            $names[] = $var['name'];
        });

        $this->loaded = true;

        return $names;
    }

    /**
     * @inheritdoc
     */
    public function loaded()
    {
        return $this->loaded;
    }

    /**
     * @inheritdoc
     */
    public function flush()
    {
        $this->loaded = false;
    }

    /**
     * @param  string $value
     * @param  string $var
     * @return string
     */
    private function resolveNested($value, $var)
    {
        $nestedValue = $this->accessor->read($var);

        return is_null($nestedValue) ? $value : str_replace('${'.$var.'}', $nestedValue, $value);
    }
}
