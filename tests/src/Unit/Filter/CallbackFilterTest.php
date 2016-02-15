<?php
/*
 * This file is part of the Gea package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gea\Tests\Unit\Filter;

use Gea\Filter\CallbackFilter;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class CallbackFilterTest extends TestCase
{
    /**
     * @dataProvider values
     * @param mixed $value
     * @param bool  $expected
     */
    public function testFilter($value, $expected)
    {
        $filter = new CallbackFilter(function ($value) {
            is_object($value) and $value = get_class($value);

            $string = is_string($value) ? $value : gettype($value);

            return "value: {$string}";
        });

        assertSame($expected, $filter->filter($value));
    }

    public function testLazy()
    {
        $filter1 = new CallbackFilter('strtolower', CallbackFilter::MODE_LAZY);
        $filter2 = new CallbackFilter('strtolower', CallbackFilter::MODE_NOT_LAZY);
        $filter3 = new CallbackFilter('strtolower');

        assertTrue($filter1->isLazy());
        assertFalse($filter2->isLazy());
        assertTrue($filter3->isLazy());
    }

    public function values()
    {
        return [
            ['foo', 'value: foo'],
            ['', 'value: '],
            [1, 'value: integer'],
            [true, 'value: boolean'],
            [[], 'value: array'],
            [(object) [], 'value: stdClass'],
            [new \ArrayObject(), 'value: ArrayObject'],
        ];
    }
}
