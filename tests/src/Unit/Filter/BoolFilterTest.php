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

use Gea\Filter\BoolFilter;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class BoolFilterTest extends TestCase
{
    /**
     * @dataProvider values
     * @param mixed $value
     * @param bool  $expected
     */
    public function testFilter($value, $expected)
    {
        $filter = new BoolFilter();

        $expected ? assertTrue($filter->filter($value)) : assertFalse($filter->filter($value));
    }

    public function testLazy()
    {
        $filter = new BoolFilter();
        assertTrue($filter->isLazy());
    }

    public function values()
    {
        return [
            ['yes', true],
            ['no', false],
            [1, true],
            [0, false],
            [null, false],
            ['', false],
            [true, true],
            [false, false],
            ['true', true],
            ['false', false],
        ];
    }
}
