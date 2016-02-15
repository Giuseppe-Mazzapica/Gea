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

use Gea\Filter\EnumFilter;
use Gea\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Gea
 */
class EnumFilterTest extends TestCase
{
    public function testFilterStrict()
    {
        $filter = new EnumFilter(['foo', '1', 2], EnumFilter::MODE_STRICT);

        assertSame('foo', $filter->filter('foo'));
        assertSame('1', $filter->filter('1'));
        assertSame(2, $filter->filter(2));
    }

    public function testFilterNotStrict()
    {
        $filter = new EnumFilter(['foo', '1', 2], EnumFilter::MODE_NOT_STRICT);

        assertSame('1', $filter->filter('1'));
        assertSame(1, $filter->filter(1));
        assertSame('2', $filter->filter('2'));
        assertSame(2, $filter->filter(2));
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     */
    public function testFilterException()
    {
        $filter = new EnumFilter(['foo', '1', 2], EnumFilter::MODE_STRICT);
        $filter->filter(1);
    }

    public function testLazy()
    {
        $filter = new EnumFilter([]);
        assertFalse($filter->isLazy());
    }
}
