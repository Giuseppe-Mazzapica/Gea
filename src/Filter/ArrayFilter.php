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
 * This filter enforce a variable to be an array, expoding its content by given separator.
 * Optionally, map the result of exploding with a given callback.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
final class ArrayFilter implements FilterInterface
{
    use LazyFilterTrait;

    const LAZY = true;
    const DO_TRIM  = 1;
    const NOT_TRIM = 0;

    /**
     * @var string
     */
    private $separator;

    /**
     * @var bool
     */
    private $trim;

    /**
     * @var callable|null
     */
    private $walker;

    /**
     * @param string        $separator
     * @param int           $flags
     * @param callable|null $walker
     */
    public function __construct($separator = ',', $flags = self::DO_TRIM, callable $walker = null)
    {
        $this->separator = $separator;
        $this->trim = is_int($flags) && ($flags & self::DO_TRIM);
        $this->walker = $walker;
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function filter($value)
    {
        if (empty($value)) {
            return [];
        }

        $array = explode($this->separator, $value);
        $this->trim and $array = array_map('trim', $array);
        $this->walker and $array = array_map($this->walker, $array);

        return $array;
    }
}
