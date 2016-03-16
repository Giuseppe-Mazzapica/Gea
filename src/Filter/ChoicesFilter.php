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
 * Filter a variable to make sure it only can assume specific values.
 * Similar to EnumFilter, this one make non-strict comparison for values.
 * This is not lazy, it means variables are checked as soon they are loaded, even if never used.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class ChoicesFilter implements FilterInterface
{
    use LazyFilterTrait;

    const LAZY = false;

    /**
     * @var array
     */
    private $allowed = [];

    /**
     * @param  array                  $args
     * @return \Gea\Filter\EnumFilter
     */
    public static function fromArray(array $args)
    {
        $instance = new static();
        $instance->allowed = $args;

        return $instance;
    }

    public function __construct()
    {
        $this->allowed = func_get_args();
    }

    /**
     * @inheritdoc
     */
    public function filter($value)
    {
        if (! in_array($value, $this->allowed)) {
            throw new FilterException(':name value is not allowed.');
        }

        return $value;
    }
}
