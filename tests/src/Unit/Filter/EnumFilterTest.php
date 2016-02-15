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
    public function testFilter()
    {
        $filter = new EnumFilter('foo', '1', 2);

        assertSame('foo', $filter->filter('foo'));
        assertSame('1', $filter->filter('1'));
        assertSame(2, $filter->filter(2));
    }

    public function testFilterFromArray()
    {
        $filter = EnumFilter::fromArray(['foo', '1', 2]);

        assertSame('foo', $filter->filter('foo'));
        assertSame('1', $filter->filter('1'));
        assertSame(2, $filter->filter(2));
    }

    /**
     * @expectedException \Gea\Exception\FilterException
     */
    public function testFilterException()
    {
        $filter = new EnumFilter('foo', '1', 2);
        $filter->filter(1);
    }

    public function testLazy()
    {
        $filter = new EnumFilter([]);
        assertFalse($filter->isLazy());
    }
}
