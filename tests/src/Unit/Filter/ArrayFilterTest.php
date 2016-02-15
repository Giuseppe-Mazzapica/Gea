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

use Gea\Filter\ArrayFilter;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class ArrayFilterTest extends TestCase
{
    public function testFilterDefault()
    {
        $filter = new ArrayFilter();

        $value = 'a,b,c';

        assertSame(['a', 'b', 'c'], $filter->filter($value));
    }

    public function testFilterWithTrim()
    {
        $filter = new ArrayFilter(',', ArrayFilter::MODE_TRIM);

        $value = 'a, b, c';

        assertSame(['a', 'b', 'c'], $filter->filter($value));
    }

    public function testFilterCustomSeparator()
    {
        $filter = new ArrayFilter('|');

        $value = 'a, b|c';

        assertSame(['a, b', 'c'], $filter->filter($value));
    }

    public function testFilterCustomSeparatorWithTrim()
    {
        $filter = new ArrayFilter('|', ArrayFilter::MODE_TRIM);

        $value = 'a, b | c';

        assertSame(['a, b', 'c'], $filter->filter($value));
    }

    public function testFilterWithWalker()
    {
        $walker = function ($value) {
            return "value: {$value}";
        };

        $filter = new ArrayFilter(',', ArrayFilter::MODE_TRIM, $walker);

        $value = 'a, b, c';

        assertSame(['value: a', 'value: b', 'value: c'], $filter->filter($value));
    }

    public function testLazy()
    {
        $filter = new ArrayFilter();
        assertTrue($filter->isLazy());
    }
}
