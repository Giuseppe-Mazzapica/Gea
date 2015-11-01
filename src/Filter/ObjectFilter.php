<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Filter;

use Gea\Exception\FilterException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class ObjectFilter implements FilterInterface
{
    use LazyFilterTrait;

    /**
     * @var bool
     */
    private static $lazy = true;

    /**
     * @var string
     */
    private $class;

    /**
     * @param string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * @inheritdoc
     */
    public function filter($value)
    {
        if (! is_string($this->class)) {
            throw new FilterException(
                sprintf(
                    ':name can\'t be filtered using %s because given class name must be in a string.',
                    __CLASS__
                )
            );
        }

        if (! class_exists($this->class)) {
            throw new FilterException(
                sprintf(
                    ':name can\'t be filtered using %s because given class name is not a class.',
                    __CLASS__
                )
            );
        }

        $class = $this->class;

        return is_null($value) ? new $class() : new $class($value);
    }
}
