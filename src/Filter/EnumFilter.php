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
final class EnumFilter implements FilterInterface
{
    use LazyFilterTrait;

    const MODE_NOT_STRICT = 0;
    const MODE_STRICT = 1;

    /**
     * @var bool
     */
    private static $lazy = false;

    /**
     * @var array
     */
    private $allowed;

    /**
     * @var bool
     */
    private $strict;

    /**
     * @param array $allowed
     * @param int   $flags
     */
    public function __construct(array $allowed, $flags = self::MODE_STRICT)
    {
        $this->allowed = $allowed;
        $this->strict = is_int($flags) && ($flags & self::MODE_STRICT);
    }

    /**
     * @inheritdoc
     */
    public function filter($value)
    {
        if (! in_array($value, $this->allowed, $this->strict)) {
            throw new FilterException(':name value is not allowed.');
        }

        return $value;
    }
}
