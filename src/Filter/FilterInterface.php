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

/**
 * Filters instances can be attached to variable names to edit the values.
 * They can do different things, like casting to types, checking existence or validity.
 * A filter may be lazy or not. A lazy filter is only applied when the variable is first accessed,
 * non-lazy filter are evaluated as soon as variables are loaded.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
interface FilterInterface
{
    /**
     * @return bool
     */
    public function isLazy();

    /**
     * @param  string $value
     * @return mixed
     */
    public function filter($value);
}
