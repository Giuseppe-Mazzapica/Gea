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

use Gea\Filter\FloatFilter;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class FloatFilterTest extends TestCase
{
    /**
     * @dataProvider values
     * @param mixed $value
     * @param float $expected
     */
    public function testFilter($value, $expected)
    {
        $filter = new FloatFilter();

        assertSame($expected, $filter->filter($value));
    }

    public function values()
    {
        return [
            ['1', 1.0],
            [2, 2.0],
            [3.0, 3.0],
            [3.523, 3.523],
            [null, 0.0],
            [0, 0.0],
            ['0', 0.0],
            ['0.0', 0.0],
            ['3.0', 3.0],
            ['3.52', 3.52],
            ['-3.52', -3.52],
            ['-0', 0.0],
            ['-0.0', 0.0],
        ];
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     */
    public function testFilterFailsIfNotNumericValues()
    {
        $filter = new FloatFilter();
        $filter->filter('m0.0');
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     */
    public function testFilterFailsIfNotWrongDecSeparator()
    {
        $filter = new FloatFilter();
        $filter->filter('2,0');
    }

    public function testLazy()
    {
        $filter = new FloatFilter();
        assertTrue($filter->isLazy());
    }
}
