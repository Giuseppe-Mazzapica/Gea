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
 * Ensure a variable is set.
 * This is a non-lazy filter: variables are checked as soon as loaded, and any error throws
 * exception even if the variable is never accessed.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class RequiredFilter implements FilterInterface
{
    use LazyFilterTrait;

    const LAZY = false;

    /**
     * @inheritdoc
     */
    public function filter($value)
    {
        if (is_null($value)) {
            throw new FilterException(':name is required.');
        }

        return $value;
    }
}
