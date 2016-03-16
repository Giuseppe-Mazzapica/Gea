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
 * Filter a variable with a given callback.
 * Can work in both lazy and non-lazy mode.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class CallbackFilter implements FilterInterface
{
    const MODE_LAZY = 1;
    const MODE_NOT_LAZY = 2;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var bool
     */
    private $lazy;

    /**
     * @param callable $callback
     * @param int      $flags
     */
    public function __construct(callable $callback, $flags = self::MODE_LAZY)
    {
        $this->callback = $callback;
        $this->lazy = is_int($flags) && ($flags & self::MODE_LAZY);
    }

    /**
     * @inheritdoc
     */
    public function filter($value)
    {
        $callback = $this->callback;

        return $callback($value);
    }

    /**
     * @inheritdoc
     */
    public function isLazy()
    {
        return $this->lazy;
    }
}
