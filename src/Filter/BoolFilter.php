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
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class BoolFilter implements FilterInterface
{
    use LazyFilterTrait;

    /**
     * @var bool
     */
    private static $lazy = true;

    /**
     * @inheritdoc
     * @return bool
     */
    public function filter($value)
    {
        return (bool) filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
