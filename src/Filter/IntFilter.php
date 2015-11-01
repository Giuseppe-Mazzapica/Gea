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
final class IntFilter implements FilterInterface
{
    use LazyFilterTrait;

    /**
     * @var bool
     */
    private static $lazy = true;

    /**
     * @inheritdoc
     * @return int
     */
    public function filter($value)
    {
        if (is_null($value)) {
            return 0;
        }
        if (! is_numeric($value)) {
            throw new FilterException(':name is not numeric.');
        }

        return (int) $value;
    }
}
